<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Mock\Repository;

use App\Mock\Entity\ActionLogEntry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

abstract class ActionLogEntryRepository extends ServiceEntityRepository
{
    public function persist(ActionLogEntry $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
