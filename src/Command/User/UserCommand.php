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
use App\Service\UserManager;
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

    protected UserManager $userManager;
    protected RoleHierarchyInterface $roleHierarchy;

    #[Required]
    public function setUserManager(UserManager $userManager): void
    {
        $this->userManager = $userManager;
    }

    #[Required]
    public function setRoleHierarchy(RoleHierarchyInterface $roleHierarchy): void
    {
        $this->roleHierarchy = $roleHierarchy;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;

        return static::SUCCESS;
    }

    protected function getUser(string $email): User
    {
        $user = $this->userManager->findUser($email);

        if (null === $user) {
            throw new RuntimeException(sprintf('Cannot find user %s', $email));
        }

        return $user;
    }

    protected function getUsers(): array
    {
        return $this->userManager->findBy([]);
    }

    /**
     * @param array|User[] $users
     */
    protected function showUsers(array $users): void
    {
        $table = new Table($this->output);
        $firstUser = reset($users);
        $headers = [
            'email',
            'roles',
            'assigned roles',
            'AEOS ID',
        ];
        if ($firstUser instanceof Timestampable) {
            $headers[] = 'created at';
            $headers[] = 'updated at';
        }
        $table->setHeaders($headers);
        foreach ($users as $user) {
            $row = [
                $user->getEmail(),
                implode(', ', $user->getRoles()),
                implode(', ', $this->roleHierarchy->getReachableRoleNames($user->getRoles())),
                $user->getAeosId(),
            ];
            if ($user instanceof Timestampable) {
                $row[] = $user->getCreatedAt()->format(\DateTimeInterface::ATOM);
                $row[] = $user->getUpdatedAt()->format(\DateTimeInterface::ATOM);
            }
            $table->addRow($row);
        }
        $table->render();
    }

    protected function showUser(User $user): void
    {
        $this->showUsers([$user]);
    }
}
