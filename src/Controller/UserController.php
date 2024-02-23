<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatableMessage;

class UserController extends AbstractDashboardController
{
    #[Route(path: '/user/apikey', name: 'user_apikey', methods: ['GET'])]
    public function apiKeyGet(): Response
    {
        return $this->render('User/apikey.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    #[Route(path: '/user/apikey', name: 'user_apikey_generate', methods: ['POST'])]
    public function apiKeyPost(UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        \assert($user instanceof User);
        $apiKey = $this->generateApiKey();
        $user->setApiKey($apiKey);
        $userRepository->persist($user, true);

        $this->addFlash('info', new TranslatableMessage('API key generated'));

        return $this->apiKeyGet();
    }

    /**
     * Generate a random string.
     *
     * @return string
     */
    private function generateApiKey(mixed $length = 30)
    {
        return base64_encode(random_bytes($length));
    }
}
