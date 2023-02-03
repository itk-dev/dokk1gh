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

class UserShowCommand extends UserCommand
{
    protected static $defaultName = 'user:show';

    protected function configure()
    {
        $this
            ->addArgument('email', InputArgument::OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $email = $input->getArgument('email');
        if (null !== $email) {
            $user = $this->getUser($email);
            $this->showUser($user);
        } else {
            $users = $this->getUsers();
            $this->showUsers($users);
        }
    }
}
