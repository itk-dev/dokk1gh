<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Mock\Entity;

use App\Mock\Repository\ActionLogEntryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'mock_action_log_entry')]
#[ORM\Entity(repositoryClass: ActionLogEntryRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[ORM\DiscriminatorMap(['aeos' => 'AeosActionLogEntry', 'sms' => 'SmsGatewayActionLogEntry'])]
abstract class ActionLogEntry
{
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected ?int $id = null;

    #[Assert\NotBlank]
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    protected ?\DateTimeInterface $createdAt = null;

    public function __construct(
        #[Assert\NotBlank]
        #[ORM\Column(type: Types::STRING)]
        protected string $type,
        #[Assert\NotBlank]
        #[ORM\Column(type: Types::JSON)]
        protected array $data
    ) {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }
}
