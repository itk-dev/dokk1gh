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
    private ?\DateTimeInterface $startTime = null;

    #[ORM\Column(type: \Doctrine\DBAL\Types\Types::DATETIME_MUTABLE)]
    #[Groups('api')]
    private ?\DateTimeInterface $endTime = null;

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

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set startTime.
     *
     * @return Code
     */
    public function setStartTime(\DateTime $startTime)
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * Get startTime.
     *
     * @return \DateTime
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * Set endTime.
     *
     * @return Code
     */
    public function setEndTime(\DateTime $endTime)
    {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * Get endTime.
     *
     * @return \DateTime
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * Set template.
     *
     * @return Code
     */
    public function setTemplate(?Template $template = null)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get template.
     *
     * @return Template
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set identifier.
     *
     * @return Code
     */
    public function setIdentifier(?string $identifier = null)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Get identifier.
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set aeosId.
     *
     * @param string $aeosId
     *
     * @return \App\Entity\Code|\App\Entity\Template
     */
    public function setAeosId($aeosId)
    {
        $this->aeosId = $aeosId;

        return $this;
    }

    /**
     * Get aeosId.
     *
     * @return string
     */
    public function getAeosId()
    {
        return $this->aeosId;
    }

    /**
     * Set note.
     *
     * @return Code
     */
    public function setNote(?string $note = null)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * Get note.
     *
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    public function getStatus()
    {
        $now = new \DateTime();
        if ($this->getStartTime() > $now) {
            return 'future';
        } elseif ($this->getEndTime() < $now) {
            return 'expired';
        }

        return 'active';
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context)
    {
        if (null === $this->getId()) {
            if ($this->getStartTime() <= new \DateTime('-1 hour')) {
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
