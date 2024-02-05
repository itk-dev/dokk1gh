<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Command\User;

use App\Entity\User;
use App\Repository\UserRepository;
use Gedmo\Timestampable\Timestampable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Contracts\Service\Attribute\Required;

abstract class UserCommand extends Command
{
    /** @var InputInterface */
    protected $input;

    /** @var OutputInterface */
    protected $output;

    protected UserRepository $userRepository;
    protected RoleHierarchyInterface $roleHierarchy;

    #[Required]
    public function setUserRepository(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    #[Required]
    public function setRoleHierarchy(RoleHierarchyInterface $roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;

        return static::SUCCESS;
    }

    protected function getUser(string $email)
    {
        $user = $this->userRepository->findOneBy(['email' => $email]);

        if (null === $user) {
            throw new RuntimeException(sprintf('Cannot find user %s', $email));
        }

        return $user;
    }

    protected function getUsers()
    {
        return $this->userRepository->findAll();
    }

    protected function showUsers(array $users)
    {
        $table = new Table($this->output);
        $firstUser = reset($users);
        $headers = [
            'email',
            'roles',
            'assigned roles',
        ];
        if ($firstUser instanceof TimestampabUser) {
            $headers[] = 'created at';
            $headers[] = 'updated at';
        }
        $table->setHeaders($headers);
        foreach ($users as $user) {
            $row = [
                $user->getEmail(),
                implode(', ', $user->getRoles()),
                implode(', ', $this->roleHierarchy->getReachableRoleNames($user->getRoles())),
            ];
            if ($user instanceof Timestampable) {
                $row[] = $user->getCreatedAt()->format(\DateTimeInterface::ATOM);
                $row[] = $user->getUpdatedAt()->format(\DateTimeInterface::ATOM);
            }
            $table->addRow($row);
        }
        $table->render();
    }

    protected function showUser(User $user)
    {
        return $this->showUsers([$user]);
    }
}
