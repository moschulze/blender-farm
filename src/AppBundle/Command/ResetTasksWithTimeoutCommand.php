<?php

namespace AppBundle\Command;

use AppBundle\Entity\Task;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ResetTasksWithTimeoutCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('tasks:resetWithTimeout')
            ->setDescription('Resets all rendering tasks that have a timeout');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $taskRepository = $doctrine->getRepository('AppBundle:Task');
        $tasksToReset = $taskRepository->findTasksWithTimeout();

        foreach($tasksToReset as $task) {
            $task->setProgress(0);
            $task->setRemaining(0);
            $task->setRuntime(0);
            $task->setStatus(Task::STATUS_PENDING);
        }

        $doctrine->getManager()->flush();
    }
}