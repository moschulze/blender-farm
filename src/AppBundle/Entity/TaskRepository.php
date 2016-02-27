<?php

namespace AppBundle\Entity;

use Doctrine\DBAL\LockMode;

class TaskRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param Project $project
     * @return integer
     */
    public function countUnfinishedTasksByProject(Project $project)
    {
        return $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.project = :project')
            ->setParameter(':project', $project)
            ->andWhere('t.status != :status')
            ->setParameter(':status', Task::STATUS_FINISHED)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param Project $project
     * @return integer
     */
    public function countFinishedTasksByProject(Project $project)
    {
        return $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.project = :project')
            ->setParameter(':project', $project)
            ->andWhere('t.status = :status')
            ->setParameter(':status', Task::STATUS_FINISHED)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param Project $project
     * @return integer
     */
    public function countRenderingTasksByProject(Project $project)
    {
        return $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.project = :project')
            ->setParameter(':project', $project)
            ->andWhere('t.status = :status')
            ->setParameter(':status', Task::STATUS_RENDERING)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return Task
     */
    public function getNextPendingTask()
    {
        $semaphore = sem_get(90210, 1, 0666, 1);
        sem_acquire($semaphore);
        try {
            $result = $this->createQueryBuilder('t')
                ->select()
                ->where('t.status = :status')
                ->setParameter(':status', Task::STATUS_PENDING)
                ->orderBy('t.id', 'ASC')
                ->setMaxResults(1)
                ->getQuery()
                ->getSingleResult();
            sem_release($semaphore);
            return $result;
        } catch(\Exception $e) {
            sem_release($semaphore);
            return null;
        }
    }

    /**
     * @return Task[]
     */
    public function findTasksWithTimeout()
    {
        $sixtySecondsAgo = new \DateTime();
        $sixtySecondsAgo->sub(new \DateInterval('PT60S'));

        return $this->createQueryBuilder('t')
            ->select()
            ->where('t.lastReport < :reportTime')
            ->andWhere('t.status = :statusRendering')
            ->setParameter(':reportTime', $sixtySecondsAgo)
            ->setParameter(':statusRendering', Task::STATUS_RENDERING)
            ->getQuery()
            ->execute();
    }
}
