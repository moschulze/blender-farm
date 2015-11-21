<?php

namespace AppBundle\Entity;

/**
 * Frame
 */
class Frame
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
     * @return Frame
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
     * @return Frame
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
     * @return Frame
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
}
