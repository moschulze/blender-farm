<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Task;
use AppBundle\Entity\Project;
use AppBundle\ProjectFileRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ApiController extends Controller
{
    public function statusAction()
    {
        return new JsonResponse(array(
            'status' => 'ok'
        ));
    }

    public function  workAction()
    {
        $tasks = $this->getDoctrine()->getRepository('AppBundle:Task')->findByStatus(Task::STATUS_PENDING);

        if(empty($tasks)) {
            return new JsonResponse(array(
                'status' => 'nothing to do',
                'id' => 0,
                'project' => 0,
                'frame' => 0,
                'format' => '',
                'engine' => '',
                'md5' => ''
            ));
        }

        /** @var Task $task */
        $task = array_shift($tasks);

        $task->setStatus(Task::STATUS_RENDERING);
        $task->getProject()->setStatus(Project::STATUS_RENDERING);
        $this->getDoctrine()->getManager()->flush();

        $fileRepository = $this->get('project_file_repository');
        $filePath = $fileRepository->getProjectFilePath($task->getProject());

        return new JsonResponse(array(
            'status' => 'ok',
            'id' => $task->getId(),
            'project' => $task->getProject()->getId(),
            'frame' => $task->getFrameNumber(),
            'format' => $task->getProject()->getFormat(),
            'engine' => $task->getProject()->getEngine(),
            'md5' => md5(file_get_contents($filePath))
        ));
    }

    public function  projectAction($id)
    {
        $project = $this->getDoctrine()->getRepository('AppBundle:Project')->find($id);

        if(is_null($project)) {
            throw new NotFoundHttpException('Project with id ' . $id);
        }

        $fileRepository = $this->get('project_file_repository');
        $filePath = $fileRepository->getProjectFilePath($project);

        if(!file_exists($filePath)) {
            throw new FileNotFoundException($filePath);
        }

        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'project.blend');
        return $response;
    }

    public function imageUploadAction(Request $request, $id)
    {
        $taskRepository = $this->getDoctrine()->getRepository('AppBundle:Task');
        $task = $taskRepository->find($id);

        if(is_null($task)) {
            throw new NotFoundHttpException('Task with id ' . $id);
        }

        $file = $request->files->get('file');
        $fileRepository = $this->get('project_file_repository');
        $fileRepository->addFrameImage(
            $file,
            $task
        );

        $task->setStatus(Task::STATUS_FINISHED);
        $task->setRemaining(0);
        $task->setProgress(1);
        $task->setRuntime($request->get('runtime'));

        $unfinishedTasks = $taskRepository->countUnfinishedTasksByProject($task->getProject());
        if($unfinishedTasks == 1) {
            $task->getProject()->setStatus(Project::STATUS_FINISHED);
        }

        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse(array(
            'status' => 'ok'
        ));
    }

    public function taskReportAction(Request $request, $id)
    {
        $task = $this->getDoctrine()
            ->getRepository('AppBundle:Task')
            ->find($id);

        if(is_null($task)) {
            throw new NotFoundHttpException('Task with id ' . $id);
        }

        $task->setRuntime($request->get('runtime'));
        $task->setRemaining($request->get('remaining'));
        $task->setProgress($request->get('progress'));
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse(array(
            'status' => 'ok'
        ));
    }
}