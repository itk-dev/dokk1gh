<?php

namespace App\Mock\Entity;

use App\Mock\Repository\AeosActionLogEntryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class AeosActionLogEntry.
 */
#[ORM\Entity(repositoryClass: AeosActionLogEntryRepository::class)]
class AeosActionLogEntry extends ActionLogEntry
{
}
