<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Entity;

use App\Repository\TemplateRepository;
use App\Trait\AeosDataEntity;
use App\Trait\BlameableEntity;
use App\Validator\Constraints as Assert;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TemplateRepository::class)]
#[UniqueEntity(fields: 'aeosId', message: 'This aeosId is already in use.')]
#[Gedmo\SoftDeleteable]
class Template implements AeosEntityInterface, \Stringable
{
    use AeosDataEntity;
    use BlameableEntity;
    use SoftDeleteableEntity;
    use TimestampableEntity;

    #[ORM\Column(type: Types::BOOLEAN)]
    protected ?bool $enabled = true;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    #[Groups('api')]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Groups('api')]
    private ?string $name = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Groups('api')]
    private ?string $level = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups('api')]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\AeosTemplateId()]
    private ?string $aeosId = null;

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setLevel(string $level): static
    {
        $this->level = $level;

        return $this;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setAeosId(string $aeosId): static
    {
        $this->aeosId = $aeosId;

        return $this;
    }

    public function getAeosId(): ?string
    {
        return $this->aeosId;
    }
}
