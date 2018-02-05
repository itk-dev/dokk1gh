<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AppBundle\Entity;

use AppBundle\Traits\BlameableEntity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Blameable;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Code.
 *
 * @ORM\Entity(repositoryClass=CodeRepository::class)
 * @Gedmo\SoftDeleteable
 * @ORM\Table
 */
class Code implements Blameable
{
    use BlameableEntity;
    use SoftDeleteableEntity;
    use TimestampableEntity;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, unique=true, nullable=true)
     */
    private $aeosId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     * @JMS\Groups({"api"})
     */
    private $startTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     * @JMS\Groups({"api"})
     */
    private $endTime;

    /**
     * @var \AppBundle\Entity\Template
     *
     * @ORM\ManyToOne(targetEntity=Template::class)
     * @ORM\JoinColumn(nullable=false)
     * @JMS\Groups({"api"})
     */
    private $template;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     * @JMS\Groups({"api"})
     * @JMS\SerializedName("code")
     */
    private $identifier;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    private $note;

    public function __toString()
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
     * @param \DateTime $startTime
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
     * @param \DateTime $endTime
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
     * @param \AppBundle\Entity\Template $template
     *
     * @return Code
     */
    public function setTemplate(Template $template = null)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get template.
     *
     * @return \AppBundle\Entity\Template
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set identifier.
     *
     * @param string $identifier
     *
     * @return Code
     */
    public function setIdentifier(string $identifier = null)
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
     * @return \AppBundle\Entity\Code|\AppBundle\Entity\Template
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
     * @param string $note
     *
     * @return Code
     */
    public function setNote(string $note = null)
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

    /**
     * @Assert\Callback
     */
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
