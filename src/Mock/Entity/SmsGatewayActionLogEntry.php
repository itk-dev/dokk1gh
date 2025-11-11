<?php

namespace App\Mock\Entity;

use App\Mock\Repository\SmsGatewayActionLogEntryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class SmsGatewayActionLogEntry.
 */
#[ORM\Entity(repositoryClass: SmsGatewayActionLogEntryRepository::class)]
class SmsGatewayActionLogEntry extends ActionLogEntry
{
}
