<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Frame;
use AppBundle\Entity\Project;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('AppBundle::index.html.twig');
    }

    public function projectIndexAction()
    {
        $projects = $this->getDoctrine()->getRepository('AppBundle:Project')->findAll();
        return $this->render('AppBundle::project_index.html.twig', array(
            'projects' => $projects
        ));
    }

    public function projectDetailAction($id)
    {
        $project = $this->getDoctrine()->getRepository('AppBundle:Project')->find($id);

        if(is_null($project)) {
            throw new NotFoundHttpException('Project with id ' . $id . ' not found');
        }

        return $this->render('AppBundle::project_detail.html.twig', array(
            'project' => $project
        ));
    }

    public function projectAddAction(Request $request)
    {
        $project = new Project();

        if($request->getMethod() == Request::METHOD_POST) {
            $project->setName($request->get('name'));
            $project->setFrameStart($request->get('frameStart'));
            $project->setFrameEnd($request->get('frameEnd'));
            $project->setStatus(Project::STATUS_NEW);
            $this->getDoctrine()->getManager()->persist($project);
            $this->getDoctrine()->getManager()->flush();

            /** @var UploadedFile $file */
            $file = $request->files->get('file');
            $filePath = __DIR__.'/../../../files/'.$project->getId().'/';
            mkdir($filePath);
            $file->move($filePath, 'project.blend');
            return $this->redirectToRoute('project_index');
        }

        return $this->render('AppBundle::project_add_edit.html.twig', array(
            'project' => $project
        ));
    }

    public function projectQueueAction(Request $request)
    {
        $id = $request->get('id');
        $doctrine = $this->getDoctrine();
        $project = $doctrine->getRepository('AppBundle:Project')->find($id);

        if(is_null($project)) {
            throw new NotFoundHttpException('Project with id ' . $id . ' not found');
        }

        if($project->getStatus() !== Project::STATUS_NEW) {
            throw new UnprocessableEntityHttpException('Project with id ' . $id . ' is not new');
        }

        for($i = $project->getFrameStart(); $i <= $project->getFrameEnd(); $i++) {
            $frame = new Frame();
            $frame->setFrameNumber($i);
            $frame->setProject($project);
            $frame->setStatus(Frame::STATUS_PENDING);
            $doctrine->getManager()->persist($frame);
        }

        $project->setStatus(Project::STATUS_QUEUED);
        $doctrine->getManager()->flush();

        return $this->redirectToRoute('project_detail', array(
            'id' => $project->getId()
        ));
    }
}
