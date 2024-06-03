<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Command\User;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'user:promote',
    description: 'Promote user'
)]
class UserPromoteCommand extends UserCommand
{
    protected function configure()
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED)
            ->addArgument('roles', InputArgument::REQUIRED | InputArgument::IS_ARRAY);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $email = $input->getArgument('email');
        $roles = $input->getArgument('roles');

        $user = $this->getUser($email);
        $user->setRoles(
            array_unique(
                array_merge(
                    $user->getRoles(),
                    $roles
                )
            )
        );
        $this->userManager->updateUser($user, true);

        $output->writeln(sprintf('User %s promoted', $user->getEmail()));
        $this->showUser($user);

        return static::SUCCESS;
    }
}
