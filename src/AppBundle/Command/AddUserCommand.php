<?php

namespace AppBundle\Command;

use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class AddUserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('user:add')
            ->setDescription('Adds a user to the system');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $encoder = $this->getContainer()->get('security.password_encoder');
        $helper = $this->getHelper('question');

        $usernameQuestion = new Question('Username: ');
        $username = $helper->ask($input, $output, $usernameQuestion);

        $emailQuestion = new Question('E-Mail: ');
        $email = $helper->ask($input, $output, $emailQuestion);

        $passwordQuestion = new Question('Password: ');
        $passwordQuestion->setHidden(true);
        $password = $helper->ask($input, $output, $passwordQuestion);

        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPassword($encoder->encodePassword($user, $password));
        $user->setRole('ROLE_USER');

        $doctrine->getManager()->persist($user);
        $doctrine->getManager()->flush();
    }
}