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
use App\Repository\UserRepository;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Timestampable\Timestampable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class UserCommand extends Command
{
    /** @var UserRepository */
    protected $userRepository;

    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var InputInterface */
    protected $input;

    /** @var OutputInterface */
    protected $output;

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        parent::__construct(null);
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;
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
            ];
            if ($user instanceof Timestampable) {
                $row[] = $user->getCreatedAt()->format(DateTimeInterface::ATOM);
                $row[] = $user->getUpdatedAt()->format(DateTimeInterface::ATOM);
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
