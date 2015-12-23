<?php

namespace AppBundle;

use AppBundle\Entity\Project;
use AppBundle\Entity\Task;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProjectFileRepository
{
    private $basePath = '';

    private $frameNumberLength = 5;

    private $imageFormats;

    public function __construct($path)
    {
        $this->basePath = rtrim($path, '/') . '/';
        $this->imageFormats = Project::$imageFormats;
    }

    private function getPathToProjectDirectory(Project $project)
    {
        return $this->basePath . $project->getId() . '/';
    }

    public function addFileToProject(File $file, Project $project)
    {
        $projectPath = $this->getPathToProjectDirectory($project);

        if(!file_exists($projectPath)) {
            mkdir($projectPath);
        }

        $fileName = $file->getFilename();
        if($file instanceof UploadedFile) {
            $fileName = $file->getClientOriginalName();
        }

        $file->move($projectPath, $fileName);
    }

    /**
     * @param File $imageFile
     * @param Task $task
     */
    public function addFrameImage(File $imageFile, Task $task)
    {
        $projectPath = $this->getPathToProjectDirectory($task->getProject());

        $fileName = sprintf(
            "frame_%'.0" . $this->frameNumberLength . "d.%s",
            $task->getFrameNumber(),
            $this->imageFormats[$task->getProject()->getFormat()]
        );

        $imageDirectoryPath = $projectPath . 'result/';

        $imageFile->move($imageDirectoryPath, $fileName);
    }

    /**
     * @param Task $task
     * @return null|File
     */
    public function getFrameImage(Task $task)
    {
        $projectPath = $this->getPathToProjectDirectory($task->getProject());
        $fileName = sprintf(
            "frame_%'.0" . $this->frameNumberLength . "d.%s",
            $task->getFrameNumber(),
            $this->imageFormats[$task->getProject()->getFormat()]
        );
        $imageDirectoryPath = $projectPath . 'result/';
        $path = $imageDirectoryPath . $fileName;
        if(!file_exists($path)) {
            return null;
        }

        return new File($path);
    }

    public function deleteProjectFiles(Project $project)
    {
        $fs = new Filesystem();
        $fs->remove($this->getPathToProjectDirectory($project));
    }

    public function getFilesForProject(Project $project, $startDirectory = '')
    {
        $projectDirectory = $this->getPathToProjectDirectory($project);

        if($startDirectory == '') {
            $startDirectory = $projectDirectory;
        }

        $result = array();

        $handle = opendir($startDirectory);
        while(($file = readdir($handle)) != false) {
            if(in_array(basename($file), array('result', '.', '..'))) {
                continue;
            }

            $file = rtrim($startDirectory, '/') . '/' . $file;

            if(is_dir($file)) {
                $subFiles = $this->getFilesForProject($project, $file);
                $result = array_merge($result, $subFiles);
            } else {
                $result[] = array(
                    'name' => basename($file),
                    'path' => str_replace($projectDirectory, '', $file),
                    'md5' => md5_file($file)
                );
            }
        }
        closedir($handle);

        return $result;
    }

    public function getFileForProject($filePath, Project $project)
    {
        $projectPath = $this->getPathToProjectDirectory($project);
        $fullPath = $projectPath . $filePath;
        $realPath = realpath($fullPath);
        if(strpos($realPath, $projectPath) !== 0) {
            return null;
        }

        if(!file_exists($realPath)) {
            return null;
        }

        return new File($realPath);
    }
}