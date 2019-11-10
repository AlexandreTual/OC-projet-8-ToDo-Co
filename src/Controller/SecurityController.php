<?php

namespace App\Controller;

use App\Form\LoginType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="login")
     *
     * @param AuthenticationUtils $authenticationUtils
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $lastUsername = $authenticationUtils->getLastUsername();
        $form = $this->createForm(LoginType::class, [$lastUsername]);
        $error = $authenticationUtils->getLastAuthenticationError();

        if (isset($error)) {
            $this->addFlash('danger', 'message.error.login');
        }

        return $this->render(
            'security/login.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/logout", name="logout")
     * @codeCoverageIgnore
     */
    public function logout()
    {
        // This code is never executed.
    }
}
