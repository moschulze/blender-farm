<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Frame;
use AppBundle\Entity\Project;
use AppBundle\ProjectFileRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class ApiController extends Controller
{
    public function  workAction()
    {
        $frames = $this->getDoctrine()->getRepository('AppBundle:Frame')->findByStatus(Frame::STATUS_PENDING);

        if(empty($frames)) {
            return new JsonResponse(array(
                'status' => 'nothing to do',
                'project' => 0,
                'frame' => 0,
                'md5' => ''
            ));
        }

        /** @var Frame $frame */
        $frame = array_shift($frames);

        $frame->setStatus(Frame::STATUS_RENDERING);
        $frame->getProject()->setStatus(Project::STATUS_RENDERING);
        $this->getDoctrine()->getManager()->flush();

        $fileRepository = new ProjectFileRepository();
        $filePath = $fileRepository->getProjectFilePath($frame->getProject()->getId());

        return new JsonResponse(array(
            'status' => 'ok',
            'project' => $frame->getProject()->getId(),
            'frame' => $frame->getFrameNumber(),
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