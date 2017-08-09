<?php

namespace AppBundle\Entity;

use AppBundle\Traits\BlameableEntity;
use AppBundle\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\SoftDeleteable\Traits\SoftDeleteableEntity;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity
 * @Gedmo\SoftDeleteable
 * @UniqueEntity(
 *   fields="email",
 *   message="This email is already in use."
 * )
 * @UniqueEntity(
 *   fields="aeosId",
 *   message="This aeosId is already in use."
 * )
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
     * @Assert\AeosPersonId
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $aeosId;

    /**
     * @ORM\Column(type="string", unique=true, nullable=true)
     */
    private $apiKey;

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
     * @return \AppBundle\Entity\Template|\AppBundle\Entity\User
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
        if ($this->getTemplates()->count() === 0) {
            $context->buildViolation('At least one template is required.')
                ->atPath('templates')
                ->addViolation();
        }
    }
}
