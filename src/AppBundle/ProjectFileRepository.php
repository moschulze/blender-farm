<?php

namespace AppBundle;

use AppBundle\Entity\Project;
use AppBundle\Entity\Task;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class ProjectFileRepository
{
    private $path = '';

    private $frameNumberLength = 5;

    private $imageFormats = array();

    public function __construct($path)
    {
        $this->path = rtrim($path, '/') . '/';
    }

    public function getProjectFilePath(Project $project)
    {
        return $this->path . $project->getId() . '/project.blend';
    }

    public function addProjectFile(File $file, Project $project)
    {
        if(!file_exists($this->path . $project->getId())) {
            mkdir($this->path . $project->getId());
        }
        $file->move($this->path . $project->getId(), 'project.blend');
    }

    public function addFrameImage(File $file, Task $task)
    {
        $fileName = sprintf(
                "frame_%'.0" . $this->frameNumberLength . "d",
                $task->getFrameNumber()
        );
        $fileName .= '.' . $this->imageFormats[$task->getProject()->getFormat()];
        $file->move($this->path . $task->getProject()->getId(), $fileName);
    }

    public function getFrameImagePath(Task $task)
    {
        $fileName = sprintf(
            "frame_%'.0" . $this->frameNumberLength . "d.%s",
            $task->getFrameNumber(),
            $this->imageFormats[$task->getProject()->getFormat()]
        );
        $path = $this->path . $task->getProject()->getId() . '/' . $fileName;
        if(!file_exists($path)) {
            return null;
        }

        return $path;
    }

    public function deleteProjectFiles(Project $project)
    {
        $path = $this->path . $project->getId();
        $fs = new Filesystem();
        $fs->remove($path);
    }

    /**
     * @param array $imageFormats
     */
    public function setImageFormats($imageFormats)
    {
        $this->imageFormats = $imageFormats;
    }
}