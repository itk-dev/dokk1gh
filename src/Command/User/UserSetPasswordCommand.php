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
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

#[AsCommand(
    name: 'user:set-password',
    description: 'Set password on user'
)]
class UserSetPasswordCommand extends UserCommand
{
    protected function configure()
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED)
            ->addArgument('password', InputArgument::OPTIONAL);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        parent::execute($input, $output);

        $email = $input->getArgument('email');
        $user = $this->getUser($email);

        $password = $input->getArgument('password');
        while (null === $password) {
            $question = (new Question('Password: '))
                ->setHidden(true);
            $helper = $this->getHelper('question');
            \assert($helper instanceof QuestionHelper);
            $password = $helper->ask($input, $output, $question);
        }

        $this->userManager->setPassword($user, $password);
        $this->userManager->updateUser($user, true);

        $output->writeln(sprintf('Password set for user %s', $user->getEmail()));
        $this->showUser($user);

        return static::SUCCESS;
    }
}
