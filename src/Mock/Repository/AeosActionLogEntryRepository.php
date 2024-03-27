<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

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
