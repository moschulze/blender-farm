<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Task;
use AppBundle\Entity\Project;
use AppBundle\Event\ProjectEvents;
use AppBundle\Event\RenderFinishedEvent;
use AppBundle\Event\RenderStartedEvent;
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

    public function workAction()
    {
        $taskRepository = $this->getDoctrine()->getRepository('AppBundle:Task');
        $task = $taskRepository->getNextPendingTask();

        if(is_null($task)) {
            return new JsonResponse(array(
                'status' => 'nothing to do',
                'id' => 0,
                'project' => array(),
                'frame' => 0,
                'format' => '',
                'engine' => ''
            ));
        }

        $fileRepository = $this->get('project_file_repository');
        $files = $fileRepository->getFilesForProject($task->getProject());

        $task->setStatus(Task::STATUS_RENDERING);
        $task->setLastReport(new \DateTime());
        $task->getProject()->setStatus(Project::STATUS_RENDERING);

        if ($taskRepository->countFinishedTasksByProject($task->getProject()) == 0
            && $taskRepository->countRenderingTasksByProject($task->getProject()) == 0
        ) {
            $this->get('event_dispatcher')->dispatch(
                ProjectEvents::RENDER_STARTED,
                new RenderStartedEvent($task->getProject())
            );
        }

        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse(array(
            'status' => 'ok',
            'id' => $task->getId(),
            'project' => array(
                'id' => $task->getProject()->getId(),
                'mainFile' => $task->getProject()->getMainFile(),
                'files' => $files
            ),
            'frame' => $task->getFrameNumber(),
            'format' => $task->getProject()->getFormat(),
            'engine' => $task->getProject()->getEngine()
        ));
    }

    public function fileAction($id, $filePath)
    {
        $project = $this->getDoctrine()->getRepository('AppBundle:Project')->find($id);

        if(is_null($project)) {
            throw new NotFoundHttpException('Project with id ' . $id . ' does not exist.');
        }

        $decodedFilePath = base64_decode(urldecode($filePath));
        $fileRepository = $this->get('project_file_repository');
        $file = $fileRepository->getFileForProject($decodedFilePath, $project);

        if(is_null($file)) {
            throw new NotFoundHttpException('File ' . $decodedFilePath . ' not found in project with id ' . $id);
        }

        $response = new BinaryFileResponse($file->getRealPath());
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT);
        return $response;
    }

    public function imageUploadAction(Request $request, $id)
    {
        $taskRepository = $this->getDoctrine()->getRepository('AppBundle:Task');
        $task = $taskRepository->find($id);

        if(is_null($task)) {
            throw new NotFoundHttpException('Task with id ' . $id . ' not found');
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

        if($taskRepository->countUnfinishedTasksByProject($task->getProject()) == 1) {
            $task->getProject()->setStatus(Project::STATUS_FINISHED);
            $this->get('event_dispatcher')->dispatch(
                ProjectEvents::RENDER_FINISHED,
                new RenderFinishedEvent($task->getProject())
            );
        } elseif ($taskRepository->countRenderingTasksByProject($task->getProject()) == 1) {
            $task->getProject()->setStatus(Project::STATUS_QUEUED);
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
        $task->setLastReport(new \DateTime());
        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse(array(
            'status' => 'ok'
        ));
    }
}