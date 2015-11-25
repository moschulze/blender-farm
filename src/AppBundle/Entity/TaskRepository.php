<?php

namespace AppBundle\Entity;

class TaskRepository extends \Doctrine\ORM\EntityRepository
{
    public function findUnfinishedTasksByProject(Project $project)
    {
        return $this->createQueryBuilder('t')
            ->select()
            ->where('t.project != :project')
            ->setParameter(':project', $project)
            ->getQuery()
            ->execute();
    }
}
