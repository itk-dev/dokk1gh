<?php

namespace App\Mock\Repository;

use App\Mock\Entity\AeosActionLogEntry;
use Doctrine\Persistence\ManagerRegistry;

final class AeosActionLogEntryRepository extends ActionLogEntryRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AeosActionLogEntry::class);
    }
}
