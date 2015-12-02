<?php

namespace AppBundle\Entity;

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
     * @return Task
     */
    public function getNextPendingTask()
    {
        $count = $this->createQueryBuilder('t')
            ->select('COUNT(t)')
            ->where('t.status = :status')
            ->setParameter(':status', Task::STATUS_PENDING)
            ->getQuery()
            ->getSingleScalarResult();

        return $this->createQueryBuilder('t')
            ->select()
            ->where('t.status = :status')
            ->setParameter(':status', Task::STATUS_PENDING)
            ->setFirstResult(rand(0, $count - 1))
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleResult();
    }
}
