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
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'user:create',
    description: 'Create user'
)]
class UserCreateCommand extends UserCommand
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED);
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

        $user = new User();
        $user
            ->setEmail($email)
            ->setPassword(sha1(uniqid((string) $email, true)));
        $this->userRepository->persist($user, true);

        $output->writeln(sprintf('User %s created', $user->getEmail()));
        $this->showUser($user);

        return static::SUCCESS;
    }
}
