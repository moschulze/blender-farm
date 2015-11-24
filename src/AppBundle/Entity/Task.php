<?php

namespace AppBundle\Entity;

/**
 * Frame
 */
class Task
{
    const STATUS_PENDING    = 'PENDING';
    const STATUS_RENDERING  = 'RENDERING';
    const STATUS_FINISHED   = 'FINISHED';

    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     */
    private $frameNumber;

    /**
     * @var string
     */
    private $status;

    /**
     * @var Project
     */
    private $project;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set frameNumber
     *
     * @param integer $frameNumber
     *
     * @return Task
     */
    public function setFrameNumber($frameNumber)
    {
        $this->frameNumber = $frameNumber;

        return $this;
    }

    /**
     * Get frameNumber
     *
     * @return integer
     */
    public function getFrameNumber()
    {
        return $this->frameNumber;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Task
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set project
     *
     * @param \AppBundle\Entity\Project $project
     *
     * @return Task
     */
    public function setProject(\AppBundle\Entity\Project $project = null)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return \AppBundle\Entity\Project
     */
    public function getProject()
    {
        return $this->project;
    }
    /**
     * @var integer
     */
    private $runtime;

    /**
     * @var integer
     */
    private $remaining;

    /**
     * @var float
     */
    private $progress;


    /**
     * Set runtime
     *
     * @param integer $runtime
     *
     * @return Task
     */
    public function setRuntime($runtime)
    {
        $this->runtime = $runtime;

        return $this;
    }

    /**
     * Get runtime
     *
     * @return integer
     */
    public function getRuntime()
    {
        return $this->runtime;
    }

    /**
     * Set remaining
     *
     * @param integer $remaining
     *
     * @return Task
     */
    public function setRemaining($remaining)
    {
        $this->remaining = $remaining;

        return $this;
    }

    /**
     * Get remaining
     *
     * @return integer
     */
    public function getRemaining()
    {
        return $this->remaining;
    }

    /**
     * Set progress
     *
     * @param float $progress
     *
     * @return Task
     */
    public function setProgress($progress)
    {
        $this->progress = $progress;

        return $this;
    }

    /**
     * Get progress
     *
     * @return float
     */
    public function getProgress()
    {
        return $this->progress;
    }
}
