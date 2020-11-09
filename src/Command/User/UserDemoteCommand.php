<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2020 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Command\User;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserDemoteCommand extends UserCommand
{
    protected static $defaultName = 'user:demote';

    protected function configure()
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED)
            ->addArgument('roles', InputArgument::REQUIRED | InputArgument::IS_ARRAY);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $email = $input->getArgument('email');
        $roles = $input->getArgument('roles');

        $user = $this->getUser($email);
        $user->setRoles(
            array_unique(
                array_diff(
                    $user->getRoles(),
                    $roles
                )
            )
        );
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln(sprintf('User %s demoted', $user->getEmail()));
        $this->showUser($user);
    }
}
