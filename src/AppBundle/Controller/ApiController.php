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

class ApiController extends Controller
{
    public function  workAction()
    {
        $tasks = $this->getDoctrine()->getRepository('AppBundle:Task')->findByStatus(Task::STATUS_PENDING);

        if(empty($tasks)) {
            return new JsonResponse(array(
                'status' => 'nothing to do',
                'id' => 0,
                'project' => 0,
                'frame' => 0,
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

    public function imageUploadAction(Request $request, $id, $frame)
    {
        $file = $request->files->get('file');
        $fileRepository = new ProjectFileRepository();
        $fileRepository->addFrameImage($file, $id, $frame);
        return new JsonResponse(array(
            'status' => 'ok'
        ));
    }
}