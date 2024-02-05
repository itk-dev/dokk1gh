<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Mock\Service;

use App\Mock\Entity\ActionLogEntry;
use App\Mock\Repository\ActionLogEntryRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;

class ActionLogManager
{
    public function __construct(private readonly EntityManagerInterface $manager, private readonly ManagerRegistry $registry)
    {
    }

    public function log(ActionLogEntry $entry)
    {
        $repository = $this->getRepository($entry::class);
        \assert($repository instanceof ActionLogEntryRepository);
        $repository->persist($entry, true);
    }

    public function findAll($className, array $criteria = [], array $orderBy = ['createdAt' => Criteria::DESC])
    {
        return $this->getRepository($className)->findBy($criteria, $orderBy);
    }

    public function findOne($className, array $criteria = [], array $orderBy = ['createdAt' => Criteria::DESC])
    {
        return $this->getRepository($className)->findOneBy($criteria, $orderBy);
    }

    private function getRepository(string $className, string $persistentManagerName = 'mock'): ObjectRepository
    {
        return $this->registry->getRepository($className, $persistentManagerName);
    }
}
