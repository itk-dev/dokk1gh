<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2020 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Entity;

use App\Repository\TemplateRepository;
use App\Traits\AeosDataEntity;
use App\Traits\BlameableEntity;
use App\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation as JMS;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=TemplateRepository::class)
 * @Gedmo\SoftDeleteable
 * @UniqueEntity(
 *   fields="aeosId",
 *   message="This aeosId is already in use."
 * )
 */
class Template implements AeosEntityInterface
{
    use AeosDataEntity;
    use BlameableEntity;
    use SoftDeleteableEntity;
    use TimestampableEntity;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    protected $enabled = true;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @JMS\Groups({"api"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @JMS\Groups({"api"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @JMS\Groups({"api"})
     */
    private $level;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     * @JMS\Groups({"api"})
     */
    private $description;

    /**
     * @var string
     *
     * @Assert\AeosTemplateId()
     *
     * @ORM\Column(type="string", length=255)
     */
    private $aeosId;

    public function __toString()
    {
        return $this->getName();
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

    public function setEnabled(bool $enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Template
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
     * Set level.
     *
     * @param string $level
     *
     * @return Template
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level.
     *
     * @return string
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return Template
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set aeosId.
     *
     * @param string $aeosId
     *
     * @return Template
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
}
