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
    name: 'user:show',
    description: 'Show user'
)]
class UserShowCommand extends UserCommand
{
    protected function configure()
    {
        $this
            ->addArgument('email', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'List of emails. Leave empty to show all users.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $emails = $input->getArgument('email');
        $users = !empty($emails)
            ? array_map($this->getUser(...), $emails)
            : $this->getUsers();
        $this->showUsers($users);

        return static::SUCCESS;
    }
}
