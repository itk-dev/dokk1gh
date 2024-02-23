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
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;

class ActionLogManager
{
    public function __construct(
        private readonly ManagerRegistry $registry
    ) {
    }

    public function log(ActionLogEntry $entry): void
    {
        $repository = $this->getRepository($entry::class);
        \assert($repository instanceof ActionLogEntryRepository);
        $repository->persist($entry, true);
    }

    public function findAll(string $className, array $criteria = [], array $orderBy = ['createdAt' => Criteria::DESC]): array
    {
        return $this->getRepository($className)->findBy($criteria, $orderBy);
    }

    public function findOne(string $className, array $criteria = [], array $orderBy = ['createdAt' => Criteria::DESC]): mixed
    {
        return $this->getRepository($className)->findOneBy($criteria);
    }

    private function getRepository(string $className, string $persistentManagerName = 'mock'): ObjectRepository
    {
        return $this->registry->getRepository($className, $persistentManagerName);
    }
}
