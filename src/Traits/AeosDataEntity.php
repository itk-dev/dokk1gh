<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2020 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Traits;

trait AeosDataEntity
{
    /**
     * Virtual property only used for displaying any AEOS template connected to this User.
     * Set by AeosEventSubscriber.
     */
    private $aeosData;

    public function setAeosData($aeosData)
    {
        $this->aeosData = $aeosData;

        return $this;
    }

    public function getAeosData()
    {
        return $this->aeosData;
    }
}
