<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2020 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Command\User;

use App\Entity\User;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UserCreateCommand extends UserCommand
{
    protected static $defaultName = 'user:create';

    protected function configure()
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $email = $input->getArgument('email');

        $user = new User();
        $user
            ->setEmail($email)
            ->setPassword(sha1(uniqid($email, true)));
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $output->writeln(sprintf('User %s created', $user->getEmail()));
        $this->showUser($user);
    }
}
