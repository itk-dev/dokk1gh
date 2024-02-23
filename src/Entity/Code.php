<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Entity;

use App\Repository\CodeRepository;
use App\Trait\BlameableEntity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Blameable;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @Gedmo\SoftDeleteable()
 */
#[ORM\Entity(repositoryClass: CodeRepository::class)]
class Code implements Blameable, \Stringable
{
    use BlameableEntity;
    use SoftDeleteableEntity;
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::INTEGER)]
    #[Groups('api')]
    private ?int $id = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, length: 255, unique: true, nullable: true)]
    private ?string $aeosId = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE)]
    #[Groups('api')]
    private ?\DateTime $startTime = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE)]
    #[Groups('api')]
    private ?\DateTime $endTime = null;

    #[ORM\ManyToOne(targetEntity: Template::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups('api')]
    private ?Template $template = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::STRING, nullable: true)]
    #[Groups('api')]
    private ?string $identifier = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::TEXT, nullable: true)]
    private ?string $note = null;

    public function __toString(): string
    {
        $timeRange = ($this->getStartTime() ? $this->getStartTime()->format(\DateTime::W3C) : null)
            .'–'
            .($this->getEndTime() ? $this->getEndTime()->format(\DateTime::W3C) : null);

        return '['.$this->getIdentifier().'; '.$this->getTemplate()->getName().'; '.$timeRange.']';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setStartTime(\DateTime $startTime): static
    {
        $this->startTime = $startTime;

        return $this;
    }

    public function getStartTime(): ?\DateTime
    {
        return $this->startTime;
    }

    public function setEndTime(\DateTime $endTime): static
    {
        $this->endTime = $endTime;

        return $this;
    }

    public function getEndTime(): ?\DateTime
    {
        return $this->endTime;
    }

    public function setTemplate(Template $template): static
    {
        $this->template = $template;

        return $this;
    }

    public function getTemplate(): ?Template
    {
        return $this->template;
    }

    public function setIdentifier(string $identifier): static
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getIdentifier(): ?string
    {
        return $this->identifier;
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

    public function setNote(?string $note = null): static
    {
        $this->note = $note;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function getStatus()
    {
        $now = new \DateTimeImmutable();

        return match (true) {
            $this->getStartTime() > $now => 'future',
            $this->getEndTime() < $now => 'expired',
            default => 'active'
        };
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context)
    {
        if (null === $this->getId()) {
            if ($this->getStartTime() <= new \DateTimeImmutable('-1 hour')) {
                $context->buildViolation('Start time must be after one hour ago.')
                    ->atPath('startTime')
                    ->addViolation();
            }
            if ($this->getStartTime() >= $this->getEndTime()) {
                $context->buildViolation('End time must be greater than start time.')
                    ->atPath('endTime')
                    ->addViolation();
            }
        }
    }
}
