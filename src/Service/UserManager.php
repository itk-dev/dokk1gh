<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig\Environment;

class UserManager
{
    /**
     * @var \FOS\UserBundle\Util\TokenGeneratorInterface
     */
    private $tokenGenerator;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Environment $twig,
        private readonly RouterInterface $router,
        private readonly MailerInterface $mailer,
        private readonly array $userManagerConfiguration
    ) {
    }

    public function createUser()
    {
        $user = new User();
        $user
            ->setPassword(sha1(uniqid('', true)))
            ->setEnabled(true);

        return $user;
    }

    public function updateUser(UserInterface $user, $andFlush = true)
    {
        $this->entityManager->persist($user);
        if ($andFlush) {
            $this->entityManager->flush();
        }
    }

    public function notifyUserCreated(UserInterface $user, $andFlush = true)
    {
        if (null === $user->getConfirmationToken()) {
            // @var $tokenGenerator TokenGeneratorInterface
            $user->setConfirmationToken($this->tokenGenerator->generateToken());
        }
        $user->setPasswordRequestedAt(new \DateTime());
        $this->updateUser($user, $andFlush);

        $message = $this->createUserCreatedMessage($user);
        $this->mailer->send($message);
    }

    public function resetPassword(User $user, $andFlush = true)
    {
        if (null === $user->getConfirmationToken()) {
            // @var $tokenGenerator TokenGeneratorInterface
            $user->setConfirmationToken($this->tokenGenerator->generateToken());
        }
        $user->setPasswordRequestedAt(new \DateTime());
        $this->updateUser($user, $andFlush);

        $this->userMailer->sendResettingEmailMessage($user);
    }

    private function createUserCreatedMessage(UserInterface $user)
    {
        $url = $this->router->generate('fos_user_resetting_reset', [
            'token' => $user->getConfirmationToken(),
            'create' => true,
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $config = $this->userManagerConfiguration->user_created;
        $sender = $config->sender;
        $template = $config->user;

        $subject = $this->twig->createTemplate($template->subject)->render([]);
        $parameters = [
            'reset_password_url' => $url,
            'user' => $user,
        ];
        $template->header = $this->twig->createTemplate($template->header)->render($parameters);
        $template->body = $this->twig->createTemplate($template->body)->render($parameters);
        $template->button->text = $this->twig->createTemplate($template->button->text)->render($parameters);
        $template->footer = $this->twig->createTemplate($template->footer)->render($parameters);

        return (new \Swift_Message($subject))
            ->setFrom($sender->email, $sender->name)
            ->setTo($user->getEmail())
            ->setBody($this->twig->render(':Emails:user_created_user.html.twig', [
                'reset_password_url' => $url,
                'header' => $template->header,
                'body' => $template->body,
                'button' => [
                    'url' => $url,
                    'text' => $template->button->text,
                ],
                'footer' => $template->footer,
            ]), 'text/html');
    }
}
