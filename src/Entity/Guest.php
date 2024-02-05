<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Entity;

use App\Repository\GuestRepository;
use App\Trait\BlameableEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Superbrave\GdprBundle\Annotation\Anonymize;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: GuestRepository::class)]
#[UniqueEntity(fields: ['phone'], message: 'This phone number is already in use.')]
#[UniqueEntity(fields: ['email'], message: 'This email is already in use.')]
class Guest
{
    use BlameableEntity;
    use SoftDeleteableEntity;
    use TimestampableEntity;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \App\Entity\Template>
     */
    #[ORM\ManyToMany(targetEntity: Template::class)]
    protected \Doctrine\Common\Collections\Collection $templates;

    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $enabled = null;

    /**
     * Time when app is sent to user.
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $sentAt = null;

    /**
     * Time when user accepts terms and conditions.
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $activatedAt = null;

    /**
     * Time when the Guest has been expired (e.g. due to inactivity).
     */
    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $expiredAt = null;

    /**
     * @todo Anonymize(type="fixed", value="{id}")
     */
    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $name = null;

    /**
     * @todo Anonymize(type="fixed", value="{id}")
     */
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $company = null;

    /**
     * @todo Anonymize(type="fixed", value="{id}")
     */
    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $phone = null;

    /**
     * @todo Anonymize(type="fixed", value="+45")
     */
    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $phoneCountryCode = null;

    /**
     * @todo Anonymize(type="fixed", value="{id}@example.com")
     */
    #[Assert\Email]
    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $startTime = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $endTime = null;

    /**
     * @var array
     */
    #[ORM\Column(type: Types::ARRAY)]
    private $timeRanges;

    public function __construct()
    {
        $this->templates = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return null|\DateTime
     */
    public function getSentAt()
    {
        return $this->sentAt;
    }

    public function setSentAt(\DateTime $sentAt)
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    /**
     * @return null|\DateTime
     */
    public function getActivatedAt()
    {
        return $this->activatedAt;
    }

    public function setActivatedAt(\DateTime $activatedAt)
    {
        $this->activatedAt = $activatedAt;

        return $this;
    }

    /**
     * @return null|\DateTime
     */
    public function getExpiredAt()
    {
        return $this->expiredAt;
    }

    public function setExpiredAt(\DateTime $expiredAt)
    {
        $this->expiredAt = $expiredAt;

        return $this;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Guest
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set company.
     *
     * @param string $company
     *
     * @return Guest
     */
    public function setCompany($company)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company.
     *
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set phone.
     *
     * @param string $phone
     *
     * @return Guest
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone.
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set phone country code.
     *
     * @param string $phoneCountryCode
     *
     * @return Guest
     */
    public function setPhoneContryCode($phoneCountryCode)
    {
        $this->phoneCountryCode = $phoneCountryCode;

        return $this;
    }

    /**
     * Get phone country code.
     *
     * @return string
     */
    public function getPhoneCountryCode()
    {
        return $this->phoneCountryCode;
    }

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return Guest
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     * @return Guest
     */
    public function setTemplates(mixed $templates)
    {
        $this->templates = $templates;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * @return Guest
     */
    public function setStartTime(\DateTime $startTime)
    {
        $this->startTime = $startTime;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * @return Guest
     */
    public function setEndTime(\DateTime $endTime)
    {
        $this->endTime = $endTime;

        return $this;
    }

    /**
     * @return array
     */
    public function getTimeRanges()
    {
        return $this->timeRanges;
    }

    public function setTimeRanges(array $timeRanges)
    {
        $this->timeRanges = $timeRanges;

        return $this;
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context)
    {
        if (0 === $this->getTemplates()->count()) {
            $context->buildViolation('At least one template is required.')
                ->atPath('templates')
                ->addViolation();
        }
        if ($this->getEndTime() <= new \DateTime()) {
            $context->buildViolation('End time must be after now.')
                ->atPath('endTime')
                ->addViolation();
        } elseif ($this->getStartTime() >= $this->getEndTime()) {
            $context->buildViolation('End time must be greater than start time.')
                ->atPath('endTime')
                ->addViolation();
        }
    }
}
