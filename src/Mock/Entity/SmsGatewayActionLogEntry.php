<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2020 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Mock\Entity;

use App\Mock\Repository\ActionLogEntryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class SmsGatewayActionLogEntry.
 *
 * @ORM\Entity(repositoryClass=ActionLogEntryRepository::class)
 */
class SmsGatewayActionLogEntry extends ActionLogEntry
{
}
