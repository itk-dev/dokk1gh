<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Mock\Entity;

use App\Mock\Repository\SmsGatewayActionLogEntryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class SmsGatewayActionLogEntry.
 */
#[ORM\Entity(repositoryClass: SmsGatewayActionLogEntryRepository::class)]
final class SmsGatewayActionLogEntry extends ActionLogEntry
{
}
