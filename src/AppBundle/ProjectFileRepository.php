<?php

namespace AppBundle;

use AppBundle\Entity\Task;
use Symfony\Component\HttpFoundation\File\File;

class ProjectFileRepository
{
    private $path = '';

    private $frameNumberLength = 5;

    public function __construct()
    {
        $this->path = __DIR__ . '/../../files/';
    }

    public function getProjectFilePath($projectId)
    {
        return $this->path . $projectId . '/project.blend';
    }

    public function addProjectFile(File $file, $projectId)
    {
        if(!file_exists($this->path . $projectId)) {
            mkdir($this->path . $projectId);
        }
        $file->move($this->path . $projectId, 'project.blend');
    }

    public function addFrameImage(File $file, $projectId, $frameNumber, $extension)
    {
        $fileName = sprintf("frame_%'.0" . $this->frameNumberLength . "d", $frameNumber) . '.' . $extension;
        $file->move($this->path . $projectId, $fileName);
    }

    public function getFrameImagePath(Task $task, $extension)
    {
        $fileName = sprintf("frame_%'.0" . $this->frameNumberLength . "d.%s", $task->getFrameNumber(), $extension);
        $path = $this->path . $task->getProject()->getId() . '/' . $fileName;
        if(!file_exists($path)) {
            return null;
        }

        return $path;
    }
}