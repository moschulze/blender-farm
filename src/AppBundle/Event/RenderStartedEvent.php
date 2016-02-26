<?php

namespace AppBundle\Event;

use AppBundle\Entity\Project;
use Symfony\Component\EventDispatcher\Event;

class RenderStartedEvent extends Event
{
    /** @var Project */
    private $project;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    public function getProject()
    {
        return $this->project;
    }
}