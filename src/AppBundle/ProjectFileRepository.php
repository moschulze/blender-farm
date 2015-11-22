<?php

namespace AppBundle;

use Symfony\Component\HttpFoundation\File\File;

class ProjectFileRepository
{
    private $path = '';

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

    public function addFrameImage(File $file, $projectId, $frameNumber)
    {
        $file->move($this->path . $projectId, sprintf("frame%'.05d", $frameNumber));
    }
}