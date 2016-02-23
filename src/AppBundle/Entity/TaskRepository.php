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
        try {
            $this->getEntityManager()->beginTransaction();
            $result = $this->createQueryBuilder('t')
                ->select()
                ->where('t.status = :status')
                ->setParameter(':status', Task::STATUS_PENDING)
                ->orderBy('t.frameNumber', 'ASC')
                ->setMaxResults(1)
                ->getQuery()
                ->setLockMode(LockMode::PESSIMISTIC_WRITE)
                ->getSingleResult();
            $this->getEntityManager()->commit();

            return $result;
        } catch(\Exception $e) {
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
