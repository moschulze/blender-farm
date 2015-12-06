<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

    public function addAction(Request $request)
    {
        $user = new User();
        $errors = array();

        if($request->getMethod() == 'POST') {
            $user->setUsername($request->get('username'));
            $user->setEmail($request->get('email'));
            $user->setPassword($request->get('password'));

            if($request->get('admin') == 'true') {
                $user->setRole('ROLE_ADMIN');
            } else {
                $user->setRole('ROLE_USER');
            }

            foreach($this->get('validator')->validate($user) as $error) {
                $errors[$error->getPropertyPath()] = $error;
            }

            if(empty($errors)) {
                $encoder = $this->get('security.password_encoder');
                $user->setPassword($encoder->encodePassword($user, $user->getPassword()));

                $doctrineManager = $this->getDoctrine()->getManager();
                $doctrineManager->persist($user);
                $doctrineManager->flush();

                return $this->redirectToRoute('user_index');
            }
        }

        return $this->render('AppBundle::user_add_edit.html.twig', array(
            'user' => $user,
            'errors' => $errors
        ));
    }

    public function editAction(Request $request, $id)
    {
        $doctrine = $this->getDoctrine();
        $user = $doctrine->getRepository('AppBundle:User')->find($id);

        if(is_null($user)) {
            throw new NotFoundHttpException('The user with the id ' . $id . ' doesn\'t exist');
        }

        $errors = array();

        if($request->getMethod() == 'POST') {
            $user->setUsername($request->get('username'));
            $user->setEmail($request->get('email'));

            if($request->get('admin') == 'true') {
                $user->setRole('ROLE_ADMIN');
            } else {
                $user->setRole('ROLE_USER');
            }

            foreach($this->get('validator')->validate($user) as $error) {
                $errors[$error->getPropertyPath()] = $error;
            }

            if(empty($errors)) {
                $password = $request->get('password');
                if(!empty($password)) {
                    $encoder = $this->get('security.password_encoder');
                    $user->setPassword($encoder->encodePassword($user, $request->get('password')));
                }

                $doctrineManager = $doctrine->getManager();
                $doctrineManager->flush();

                return $this->redirectToRoute('user_index');
            }
        }

        return $this->render('AppBundle::user_add_edit.html.twig', array(
            'user' => $user,
            'errors' => $errors
        ));
    }
}