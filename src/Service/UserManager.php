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
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Twig\Environment;

class UserManager
{
    /**
     * @phpstan-param array<string, mixed> $options
     */
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ResetPasswordHelperInterface $resetPasswordHelper,
        private readonly Environment $twig,
        private readonly RouterInterface $router,
        private readonly MailerInterface $mailer,
        private readonly array $options
    ) {
    }

    public function createUser(): User
    {
        return (new User())
            ->setPassword(sha1(uniqid('', true)))
            ->setEnabled(true);
    }

    public function findUser(string $email): ?User
    {
        return $this->userRepository->findOneBy(['email' => $email]);
    }

    /**
     * @return array|User[]
     */
    public function findBy(array $criteria, ?array $orderBy = null, ?int $limit = null, ?int $offset = null): array
    {
        return $this->userRepository->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function updateUser(User $user, bool $flush = true): void
    {
        $this->userRepository->persist($user, $flush);
    }

    public function setPassword(User $user, string $password): User
    {
        return $user->setPassword($this->passwordHasher->hashPassword(
            $user,
            $password
        ));
    }

    public function notifyUserCreated(User $user, bool $flush = true): void
    {
        $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        $message = $this->createUserCreatedMessage($user, $resetToken);
        $this->mailer->send($message);
    }

    private function createUserCreatedMessage(User $user, ResetPasswordToken $resetPasswordToken): Email
    {
        $url = $this->router->generate('app_reset_password', [
            'token' => $resetPasswordToken->getToken(),
            'create' => true,
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        $parameters = [
            'reset_password_url' => $url,
            'user' => $user,
        ];

        $template = $this->options['user_created'];
        $subject = $this->twig->createTemplate($template['subject'])->render($parameters);
        $template['header'] = $this->twig->createTemplate($template['header'])->render($parameters);
        $template['body'] = $this->twig->createTemplate($template['body'])->render($parameters);
        $template['button']['text'] = $this->twig->createTemplate($template['button']['text'])->render($parameters);
        $template['button']['url'] = $url;
        $template['footer'] = $this->twig->createTemplate($template['footer'])->render($parameters);

        return (new TemplatedEmail())
            ->from(new Address(
                $this->options['sender']['from_email'],
                $this->options['sender']['from_name']
            ))
            ->to($user->getEmail())
            ->subject($subject)
            ->htmlTemplate('Emails/user_created_user.html.twig')
            ->context($template + [
                'reset_password_url' => $url,
                'user' => $user,
            ]);
    }
}
