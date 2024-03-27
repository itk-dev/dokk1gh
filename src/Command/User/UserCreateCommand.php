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
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'user:create',
    description: 'Create user'
)]
class UserCreateCommand extends UserCommand
{
    protected function configure()
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED)
            ->addOption('notify', null, InputOption::VALUE_NONE, 'Notify user');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $email = $input->getArgument('email');

        try {
            $user = $this->getUser($email);
        } catch (\Throwable) {
        }
        if (isset($user)) {
            throw new RuntimeException(sprintf('User %s already exists', $user->getUserIdentifier()));
        }

        $user = $this->userManager
            ->createUser()
            ->setEmail($email);
        $this->userManager->updateUser($user, true);

        $output->writeln(sprintf('User %s created', $user->getEmail()));
        $this->showUser($user);

        if ($input->getOption('notify')) {
            $this->userManager->notifyUserCreated($user);
        }

        return static::SUCCESS;
    }
}
