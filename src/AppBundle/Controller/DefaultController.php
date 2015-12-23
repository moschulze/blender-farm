<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Task;
use AppBundle\Entity\Project;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\ConstraintViolation;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->redirectToRoute('project_index');
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
        $errors = array();

        if($request->getMethod() == Request::METHOD_POST) {
            $project->setName($request->get('name'));
            $project->setFrameStart($request->get('frameStart'));
            $project->setFrameEnd($request->get('frameEnd'));
            $project->setFormat($request->get('format'));
            $project->setEngine($request->get('engine'));
            $project->setStatus(Project::STATUS_NEW);

            $validator = $this->get('validator');
            $violations = $validator->validate($project);

            /** @var UploadedFile $file */
            $file = null;
            if(
                !$request->files->has('file')
                || is_null($request->files->get('file'))
                || $request->files->get('file')->getClientOriginalExtension() != 'blend'
            ) {
                $errors['file'] = new ConstraintViolation(
                    'Please upload a valid blender file',
                    'Please upload a valid blender file',
                    array(),
                    $project,
                    'file',
                    null
                );
            } else {
                $file = $request->files->get('file');
            }

            foreach($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation;
            }

            if(empty($errors)) {
                $project->setMainFile($file->getClientOriginalName());
                $this->getDoctrine()->getManager()->persist($project);
                $this->getDoctrine()->getManager()->flush();

                $fileRepository = $this->get('project_file_repository');
                $fileRepository->addFileToProject($file, $project);
                return $this->redirectToRoute('project_detail', array(
                    'id' => $project->getId()
                ));
            }
        }

        return $this->render('AppBundle::project_add_edit.html.twig', array(
            'project' => $project,
            'imageFormats' => Project::$imageFormats,
            'renderingEngines' => Project::$engines,
            'errors' => $errors
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
            $task = new Task();
            $task->setFrameNumber($i);
            $task->setProject($project);
            $task->setStatus(Task::STATUS_PENDING);
            $task->setRuntime(0);
            $task->setRemaining(0);
            $task->setProgress(0);
            $task->setLastReport(new \DateTime());
            $doctrine->getManager()->persist($task);
        }

        $project->setStatus(Project::STATUS_QUEUED);
        $doctrine->getManager()->flush();

        return $this->redirectToRoute('project_detail', array(
            'id' => $project->getId()
        ));
    }

    public function taskImageAction($id)
    {
        $task = $this->getDoctrine()->getRepository('AppBundle:Task')->find($id);

        if(is_null($task)) {
            throw new NotFoundHttpException('task with id ' . $id);
        }

        $fileRepository = $this->get('project_file_repository');
        $image = $fileRepository->getFrameImage($task);

        if(is_null($image)) {
            throw new NotFoundHttpException('image for task with id ' . $id);
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $image->getRealPath());
        finfo_close($finfo);
        return new BinaryFileResponse(
            $image->getRealPath(),
            200,
            array(
                'Content-Type' => $mimeType
            )
        );
    }

    public function projectDownloadAction($id)
    {
        $project = $this->getDoctrine()->getRepository('AppBundle:Project')->find($id);
        $fileRepository =$this->get('project_file_repository');

        if(is_null($project)) {
            throw new NotFoundHttpException('Project with id ' . $id);
        }

        if($project->getStatus() !== Project::STATUS_FINISHED) {
            throw new NotFoundHttpException('Project with id ' . $id . ' not finished');
        }

        $file = tempnam('tmp', md5(microtime() . rand()));
        $zip = new \ZipArchive();
        $zip->open($file, \ZipArchive::OVERWRITE);
        foreach($project->getTasks() as $task) {
            $file = $fileRepository->getFrameImage($task);
            $zip->addFile($file->getRealPath(), $file->getBasename());
        }
        $zip->close();

        $response = new BinaryFileResponse(
            $file,
            200,
            array(
                'Content-Type' => 'application/zip',
                'Content-Length' => filesize($file),
                'Content-Disposition' => 'attachment; filename="file.zip"'
            )
        );
        $response->deleteFileAfterSend(true);

        return $response;
    }

    public function projectStatusAction($id)
    {
        $project = $this->getDoctrine()->getRepository('AppBundle:Project')->find($id);

        if(is_null($project)) {
            throw new NotFoundHttpException('Project with id ' . $id);
        }

        $tasks = array();
        foreach($project->getTasks() as $task) {
            $tasks[] = array(
                'id' => $task->getId(),
                'frameNumber' => $task->getFrameNumber(),
                'status' => $task->getStatus(),
                'runtime' => $task->getRuntime(),
                'remaining' => $task->getRemaining(),
                'progress' => $task->getProgress()
            );
        }

        $data = array(
            'status' => $project->getStatus(),
            'tasks' => $tasks
        );

        return new JsonResponse($data);
    }

    public function projectDeleteAction($id)
    {
        $manager = $this->getDoctrine()->getManager();
        $project = $this->getDoctrine()->getRepository('AppBundle:Project')->find($id);

        if(is_null($project)) {
            throw new NotFoundHttpException('Project with id ' . $id . ' not found');
        }

        $this->get('project_file_repository')->deleteProjectFiles($project);

        foreach($project->getTasks() as $task) {
            $manager->remove($task);
        }

        $manager->remove($project);
        $manager->flush();

        return $this->redirectToRoute('project_index');
    }
}
