<?php

namespace AppBundle\Service;

use AppBundle\Entity\User;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Doctrine\UserManager as BaseUserManager;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Util\CanonicalFieldsUpdater;
use FOS\UserBundle\Util\PasswordUpdaterInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class UserManager extends BaseUserManager
{
    /** @var \Twig_Environment */
    private $twig;

    /** @var \Symfony\Component\Routing\RouterInterface */
    private $router;

    /** @var \Swift_Mailer */
    private $mailer;

    /** @var array  */
    private $configuration;

    public function __construct(PasswordUpdaterInterface $passwordUpdater, CanonicalFieldsUpdater $canonicalFieldsUpdater, ObjectManager $om, $class, TokenGeneratorInterface $tokenGenerator, \Twig_Environment $twig, RouterInterface $router, \Swift_Mailer $mailer, array $configuration)
    {
        parent::__construct($passwordUpdater, $canonicalFieldsUpdater, $om, $class);
        $this->tokenGenerator = $tokenGenerator;
        $this->twig = $twig;
        $this->router = $router;
        $this->mailer = $mailer;
        $this->configuration = json_decode(json_encode($configuration));
    }

    public function createUser()
    {
        $user = parent::createUser();

        $user->setPlainPassword(uniqid());
        $user->setEnabled(true);

        return $user;
    }

    public function updateUser(UserInterface $user, $andFlush = true)
    {
        $user->setUsername($user->getEmail());

        parent::updateUser($user, $andFlush);
    }

    public function notifyUserCreated(User $user, $andFlush = true)
    {
        if (null === $user->getConfirmationToken()) {
            /** @var $tokenGenerator TokenGeneratorInterface */
            $user->setConfirmationToken($this->tokenGenerator->generateToken());
        }
        $user->setPasswordRequestedAt(new \DateTime());
        $this->updateUser($user, $andFlush);

        $message = $this->createUserCreatedMessage($user);
        $this->mailer->send($message);
    }

    private function createUserCreatedMessage(UserInterface $user) {
        $url = $this->router->generate('fos_user_resetting_reset', ['token' => $user->getConfirmationToken()], UrlGeneratorInterface::ABSOLUTE_URL);

        $config = $this->configuration->user_created;
        $sender = $config->sender;
        $template = $config->user;

        $subject = $this->twig->createTemplate($template->subject)->render([]);
        $content = $this->twig->createTemplate($template->body)->render([
            'reset_password_url' => $url,
            'user' => $user,
            'sender' => $config->sender,
        ]);

        return (new \Swift_Message($subject))
            ->setFrom($sender->email, $sender->name)
            ->setTo($user->getEmail())
            ->setBody($this->twig->render(':Emails:user_created_user.html.twig', [
                'content' => $content,
            ]), 'text/html');
    }
}
