<?php

namespace AppBundle\Entity;

use AppBundle\Traits\BlameableEntity;
use AppBundle\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation as JMS;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Template
 *
 * @ORM\Entity
 * @Gedmo\SoftDeleteable
 * @UniqueEntity(
 *   fields="aeosId",
 *   message="This aeosId is already in use."
 * )
 * @ORM\Table
 */
class Template
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
     * @Assert\AeosTemplateId()
     *
     * @ORM\Column(type="string", length=255)
     */
    private $aeosId;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
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
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set aeosId
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
     * Get aeosId
     *
     * @return string
     */
    public function getAeosId()
    {
        return $this->aeosId;
    }

    /**
     * Virtual property only used for displaying any AEOS template connected to this User.
     */
    private $aeosData;

    public function setAeosData($aeosTemplate)
    {
        $this->aeosData = $aeosTemplate;

        return $this;
    }

    public function getAeosData()
    {
        return $this->aeosData;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
