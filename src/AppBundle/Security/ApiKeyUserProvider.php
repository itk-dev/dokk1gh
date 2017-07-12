<?php

namespace AppBundle\Security;

use FOS\UserBundle\Doctrine\UserManager;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;

class ApiKeyUserProvider implements UserProviderInterface
{
    private $userManager;

    public function __construct(UserManager $userManager) {
        $this->userManager = $userManager;
    }

    public function getUsernameForApiKey($apiKey)
    {
        $user = $this->userManager->findUserBy(['apiKey' => $apiKey]);
        return $user ? $user->getUsername() : null;
    }

    public function loadUserByUsername($username)
    {
        return $this->userManager->findUserByUsernameOrEmail($username);
    }

    public function refreshUser(UserInterface $user)
    {
        // this is used for storing authentication in the session
        // but in this example, the token is sent in each request,
        // so authentication can be stateless. Throwing this exception
        // is proper to make things stateless
        throw new UnsupportedUserException();
    }

    public function supportsClass($class)
    {
        return User::class === $class;
    }
}
