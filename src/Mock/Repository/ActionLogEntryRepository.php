<?php

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
