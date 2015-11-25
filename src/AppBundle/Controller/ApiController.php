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

        $fileRepository = new ProjectFileRepository();
        $filePath = $fileRepository->getProjectFilePath($task->getProject()->getId());

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
        $fileRepository = new ProjectFileRepository();
        $filePath = $fileRepository->getProjectFilePath($id);

        if(!file_exists($filePath)) {
            throw new FileNotFoundException($filePath);
        }

        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, 'project.blend');
        return $response;
    }

    public function imageUploadAction(Request $request, $id)
    {
        $task = $this->getDoctrine()
            ->getRepository('AppBundle:Task')
            ->find($id);

        if(is_null($task)) {
            throw new NotFoundHttpException('Task with id ' . $id);
        }

        $imageFormats = $this->getParameter('image_formats');
        $fileExtension = $imageFormats[$task->getProject()->getFormat()];

        $file = $request->files->get('file');
        $fileRepository = new ProjectFileRepository();
        $fileRepository->addFrameImage(
            $file,
            $task->getProject()->getId(),
            $task->getFrameNumber(),
            $fileExtension
        );

        $task->setStatus(Task::STATUS_FINISHED);
        $task->setRemaining(0);
        $task->setProgress(1);
        $task->setRuntime($request->get('runtime'));
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