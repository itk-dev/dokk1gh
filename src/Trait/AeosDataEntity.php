<?php

namespace App\Trait;

trait AeosDataEntity
{
    /**
     * Virtual property only used for displaying any AEOS template connected to this entity.
     * Set by EasyAdminSubscriber.
     *
     * @see \App\EventSubscriber\AeosEventSubscriber::setAeosData().
     *
     * @phpstan-var array<string, mixed>
     */
    private ?array $aeosData = null;

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
