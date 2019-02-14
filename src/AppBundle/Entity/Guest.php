<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AppBundle\Entity;

use AppBundle\Traits\BlameableEntity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Blameable\Blameable;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Superbrave\GdprBundle\Annotation\Anonymize;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Guest.
 *
 * @ORM\Table
 * @ORM\Entity
 * @UniqueEntity(
 *     fields={"phone"},
 *     message="This phone number is already in use."
 * )
 * @UniqueEntity(
 *     fields={"email"},
 *     message="This email is already in use."
 * )
 */
class Guest implements Blameable
{
    use BlameableEntity;
    use SoftDeleteableEntity;
    use TimestampableEntity;

    /**
     * @ORM\ManyToMany(targetEntity=Template::class)
     */
    protected $templates;

    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="guid")
     * @ORM\GeneratedValue(strategy="UUID")
     */
    private $id;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    /**
     * Time when app is sent to user.
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $sentAt;

    /**
     * Time when user accepts terms and conditions.
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $activatedAt;

    /**
     * Time when the Guest has been expired (e.g. due to inactivity).
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $expiredAt;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Anonymize(type="fixed", value="{id}")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Anonymize(type="fixed", value="{id}")
     */
    private $company;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Anonymize(type="fixed", value="{id}")
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Anonymize(type="fixed", value="+45")
     */
    private $phoneCountryCode;

    /**
     * @var string
     *
     * @Assert\Email()
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Anonymize(type="fixed", value="{id}@example.com")
     */
    private $email;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $startTime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $endTime;

    /**
     * @var array
     *
     * @ORM\Column(type="array")
     */
    private $timeRanges;

    /**
     * Get id.
     *
     * @return string
     */
    public function getId()
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

    /**
     * @param bool $enabled
     */
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

    /**
     * @param \DateTime $sentAt
     */
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

    /**
     * @param \DateTime $activatedAt
     */
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

    /**
     * @param \DateTime $expiredAt
     */
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

    /**
     * @return mixed
     */
    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     * @param mixed $templates
     *
     * @return Guest
     */
    public function setTemplates($templates)
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
     * @param \DateTime $startTime
     *
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
     * @param \DateTime $endTime
     *
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

    /**
     * @param array $timeRanges
     */
    public function setTimeRanges(array $timeRanges)
    {
        $this->timeRanges = $timeRanges;

        return $this;
    }

    /**
     * @Assert\Callback()
     */
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
