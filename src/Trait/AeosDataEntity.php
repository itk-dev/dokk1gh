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
     *
     * @phpstan-var array<string, mixed>
     */
    private array $aeosData;

    /**
     * @phpstan-param array<string, mixed> $aeosData
     */
    public function setAeosData(array $aeosData): static
    {
        $this->aeosData = $aeosData;

        return $this;
    }

    /**
     * @phpstan-return array<string, mixed>
     */
    public function getAeosData(): ?array
    {
        return $this->aeosData;
    }
}
