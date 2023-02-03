<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2020 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Entity;

use App\Repository\UserRepository;
use App\Traits\AeosDataEntity;
use App\Traits\BlameableEntity;
use App\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Timestampable;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use ItkDev\UserBundle\Entity\User as BaseUser;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @Gedmo\SoftDeleteable
 * @UniqueEntity(
 *   fields="email",
 *   message="This email is already in use."
 * )
 * @UniqueEntity(
 *   fields="aeosId",
 *   message="This aeosId is already in use."
 * )
 */
class User extends BaseUser implements UserInterface, Timestampable, AeosEntityInterface
{
    use AeosDataEntity;
    use BlameableEntity;
    use SoftDeleteableEntity;
    use TimestampableEntity;

    /**
     * @var \DateTime
     * @ORM\Column(name="gdpr_accepted_at", type="datetime", nullable=true)
     */
    protected $gdprAcceptedAt;

    /**
     * @ORM\ManyToMany(targetEntity=Template::class)
     */
    protected $templates;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @Assert\AeosPersonId
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $aeosId;

    /**
     * @ORM\Column(type="string", unique=true, nullable=true)
     */
    private $apiKey;

    public function __construct()
    {
        $this->templates = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGdprAcceptedAt()
    {
        return $this->gdprAcceptedAt;
    }

    public function setGdprAcceptedAt($gdprAcceptedAt)
    {
        $this->gdprAcceptedAt = $gdprAcceptedAt;

        return $this;
    }

    public function setTemplates(ArrayCollection $templates)
    {
        $this->templates = $templates;

        return $this;
    }

    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     * Set aeosId.
     *
     * @param string $aeosId
     *
     * @return \App\Entity\Template|\App\Entity\User
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

    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function getApiKey()
    {
        return $this->apiKey;
    }

    /**
     * @Callback
     */
    public function validate(ExecutionContextInterface $context)
    {
        if (0 === $this->getTemplates()->count()) {
            $context->buildViolation('At least one template is required.')
                ->atPath('templates')
                ->addViolation();
        }
    }
}
