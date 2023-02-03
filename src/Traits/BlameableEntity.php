<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2020 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Traits;

use App\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

trait BlameableEntity
{
    /**
     * @var User
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    protected $createdBy;

    /**
     * @var User
     * @Gedmo\Blameable(on="update")
     * @ORM\ManyToOne(targetEntity=User::class)
     * @ORM\JoinColumn(referencedColumnName="id")
     */
    protected $updatedBy;

    /**
     * Sets createdBy.
     *
     * @return $this
     */
    public function setCreatedBy(User $createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Returns createdBy.
     *
     * @return User
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Sets updatedBy.
     *
     * @return $this
     */
    public function setUpdatedBy(User $updatedBy)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Returns updatedBy.
     *
     * @return User
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }
}
