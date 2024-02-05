<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Trait;

trait AeosDataEntity
{
    /**
     * Virtual property only used for displaying any AEOS template connected to this entity.
     * Set by AeosEventSubscriber.
     */
    private $aeosData;

    public function setAeosData(array $aeosData)
    {
        $this->aeosData = $aeosData;

        return $this;
    }

    public function getAeosData(): ?array
    {
        return $this->aeosData;
    }
}
