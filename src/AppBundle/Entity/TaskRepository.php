<?php

namespace AppBundle\Entity;

class TaskRepository extends \Doctrine\ORM\EntityRepository
{
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
}
