<?php

namespace AppBundle\Entity;

/**
 * Project
 */
class Project
{
    const STATUS_NEW        = 'NEW';
    const STATUS_QUEUED     = 'QUEUED';
    const STATUS_RENDERING  = 'RENDERING';
    const STATUS_PAUSED     = 'PAUSED';
    const STATUS_STOPPED    = 'STOPPED';
    const STATUS_FINISHED   = 'FINISHED';

    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $name;


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
     * Set name
     *
     * @param string $name
     *
     * @return Project
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    /**
     * @var string
     */
    private $status;


    /**
     * Set status
     *
     * @param string $status
     *
     * @return Project
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
     * @var integer
     */
    private $frameStart;

    /**
     * @var integer
     */
    private $frameEnd;


    /**
     * Set frameStart
     *
     * @param integer $frameStart
     *
     * @return Project
     */
    public function setFrameStart($frameStart)
    {
        $this->frameStart = $frameStart;

        return $this;
    }

    /**
     * Get frameStart
     *
     * @return integer
     */
    public function getFrameStart()
    {
        return $this->frameStart;
    }

    /**
     * Set frameEnd
     *
     * @param integer $frameEnd
     *
     * @return Project
     */
    public function setFrameEnd($frameEnd)
    {
        $this->frameEnd = $frameEnd;

        return $this;
    }

    /**
     * Get frameEnd
     *
     * @return integer
     */
    public function getFrameEnd()
    {
        return $this->frameEnd;
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $frames;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->frames = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add frame
     *
     * @param \AppBundle\Entity\Frame $frame
     *
     * @return Project
     */
    public function addFrame(\AppBundle\Entity\Frame $frame)
    {
        $this->frames[] = $frame;

        return $this;
    }

    /**
     * Remove frame
     *
     * @param \AppBundle\Entity\Frame $frame
     */
    public function removeFrame(\AppBundle\Entity\Frame $frame)
    {
        $this->frames->removeElement($frame);
    }

    /**
     * Get frames
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFrames()
    {
        return $this->frames;
    }
}
