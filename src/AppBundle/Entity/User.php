<?php

namespace AppBundle\Entity;

use AppBundle\Traits\BlameableEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity
 * @Gedmo\SoftDeleteable
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    use BlameableEntity;
    use SoftDeleteableEntity;
    use TimestampableEntity;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, unique=true, nullable=true)
     */
    private $aeosId;

    /**
     * @ORM\ManyToMany(targetEntity=Template::class)
     */
    protected $templates;

    public function __construct()
    {
        parent::__construct();
        $this->templates = new ArrayCollection();
    }

    public function getTemplates()
    {
        return $this->templates;
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
}
