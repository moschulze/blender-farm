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

    public static $imageFormats = array(
        'PNG' => 'png',
        'JPEG' => 'jpg'
    );

    public static $engines = array(
        'BLENDER_RENDER' => 'Blender Render',
        'CYCLES' => 'Cycles'
    );

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
    private $tasks;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->tasks = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add task
     *
     * @param \AppBundle\Entity\Task $task
     *
     * @return Project
     */
    public function addTask(\AppBundle\Entity\Task $task)
    {
        $this->tasks[] = $task;

        return $this;
    }

    /**
     * Remove task
     *
     * @param \AppBundle\Entity\Task $task
     */
    public function removeTask(\AppBundle\Entity\Task $task)
    {
        $this->tasks->removeElement($task);
    }

    /**
     * Get tasks
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTasks()
    {
        return $this->tasks;
    }
    /**
     * @var string
     */
    private $format;


    /**
     * Set format
     *
     * @param string $format
     *
     * @return Project
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Get format
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @return array
     */
    public static function getImageFormats()
    {
        return array_keys(self::$imageFormats);
    }

    /**
     * @var string
     */
    private $engine;


    /**
     * Set engine
     *
     * @param string $engine
     *
     * @return Project
     */
    public function setEngine($engine)
    {
        $this->engine = $engine;

        return $this;
    }

    /**
     * Get engine
     *
     * @return string
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * @return array
     */
    public static function getEngines()
    {
        return array_keys(self::$engines);
    }
    /**
     * @var string
     */
    private $mainFile;


    /**
     * Set mainFile
     *
     * @param string $mainFile
     *
     * @return Project
     */
    public function setMainFile($mainFile)
    {
        $this->mainFile = $mainFile;

        return $this;
    }

    /**
     * Get mainFile
     *
     * @return string
     */
    public function getMainFile()
    {
        return $this->mainFile;
    }

    /**
     * @return int
     */
    public function getRenderTime()
    {
        $runtime = 0;
        /** @var Task $task */
        foreach($this->tasks as $task) {
            $runtime += $task->getRuntime();
        }
        return $runtime;
    }

    /**
     * @return float
     */
    public function getProgress()
    {
        if($this->tasks->count() == 0) {
            return 0;
        }

        $progress = 0;
        /** @var Task $task */
        foreach($this->tasks as $task) {
            $progress += $task->getProgress();
        }
        return ($progress / $this->tasks->count());
    }

    /**
     * @return double
     */
    public function getAverageFrameRenderingTime()
    {
        $count = 0;
        $sum = 0;
        /** @var Task $task */
        foreach($this->tasks as $task) {
            if($task->getStatus() == Task::STATUS_FINISHED) {
                $count++;
                $sum += $task->getRuntime();
            }
        }

        if($count > 0) {
            return $sum / $count;
        }

        return 0;
    }
}
