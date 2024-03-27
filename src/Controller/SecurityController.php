<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Translation\TranslatableMessage;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // @see https://symfony.com/bundles/EasyAdminBundle/current/dashboards.html#login-form-template
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('admin/page/login.html.twig', [
            'page_title' => $this->getParameter('site_name'),

            // parameters usually defined in Symfony login forms
            'error' => $error,
            'last_username' => $lastUsername,

            'csrf_token_intention' => 'authenticate',
            'username_label' => new TranslatableMessage('Email'),
            'password_label' => new TranslatableMessage('Password'),
            'sign_in_label' => new TranslatableMessage('Log in'),

            'forgot_password_enabled' => true,
            'forgot_password_path' => $this->generateUrl('app_forgot_password_request'),
            'forgot_password_label' => new TranslatableMessage('Forgot your password?'),
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
