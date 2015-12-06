<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class UserController extends Controller
{
    private $usersPerPage = 10;

    public function loginAction()
    {
        $authenticationUtils = $this->get('security.authentication_utils');
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('AppBundle::login.html.twig', array(
            'error' => $error,
            'lastUsername' => $lastUsername
        ));
    }

    public function indexAction($page)
    {
        $userRepository = $this->getDoctrine()->getRepository('AppBundle:User');

        $users = $userRepository->findBy(
            array(),
            array(),
            $this->usersPerPage,
            ($page - 1) * $this->usersPerPage
        );

        $userCount = $userRepository->countAll();

        return $this->render('AppBundle::user_index.html.twig', array(
            'users' => $users,
            'pages' => (ceil($userCount / $this->usersPerPage)),
            'page' => $page
        ));
    }
}