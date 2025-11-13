<?php

namespace App\Mock\Repository;

use App\Mock\Entity\SmsGatewayActionLogEntry;
use Doctrine\Persistence\ManagerRegistry;

final class SmsGatewayActionLogEntryRepository extends ActionLogEntryRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SmsGatewayActionLogEntry::class);
    }
}
