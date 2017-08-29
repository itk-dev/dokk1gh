<?php

namespace AppBundle\Command;

use AppBundle\Service\AeosService;
use AppBundle\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Exception\InvalidArgumentException;

class DebugCommand extends AbstractBaseCommand
{
    /** @var \Doctrine\ORM\EntityManagerInterface */
    protected $entityManager;

    /** @var \AppBundle\Service\AeosService */
    protected $aeosService;

    /** @var \AppBundle\Service\UserManager */
    protected $userManager;

    public function __construct(EntityManagerInterface $entityManager, AeosService $aeosService, UserManager $userManager)
    {
        parent::__construct('app:debug');
        $this->entityManager = $entityManager;
        $this->aeosService = $aeosService;
        $this->userManager = $userManager;
    }

    /**
     * Debug email sent to new user.
     *
     * @command
     *
     * @param mixed $username
     */
    private function notifyUserCreated($username)
    {
        /** @var \AppBundle\Entity\User $user */
        $user = $this->userManager->findUserByUsernameOrEmail($username);

        if (!$user) {
            throw new InvalidArgumentException('No such user: '.$username);
        }

        $this->userManager->notifyUserCreated($user);

        $method = new \ReflectionMethod($this->userManager, 'createUserCreatedMessage');
        $method->setAccessible(true);
        /** @var \Swift_Message $message */
        $message = $method->invoke($this->userManager, $user);

        $this->output->writeln([
            str_repeat('=', 80),
            $message,
            str_repeat('-', 80),
            $message->getBody(),
            str_repeat('=', 80),
        ]);
    }

    /**
     * @command
     *
     * @param string $username the username (email)
     */
    private function resetPassword($username)
    {
        $user = $this->userManager->findUserByUsernameOrEmail($username);

        if (!$user) {
            throw new InvalidArgumentException('No such user: '.$username);
        }

        $this->getContainer()->get('fos_user.mailer')->sendResettingEmailMessage($user);
        $user->setPasswordRequestedAt(new \DateTime());
        $this->getContainer()->get('fos_user.user_manager')->updateUser($user);
    }
}
