<?php

namespace AppBundle\Event;

class ProjectEvents
{
    const QUEUED = 'project.queued';

    const RENDER_STARTED = 'project.render_started';
    const RENDER_FINISHED = 'project.render_finished';
}