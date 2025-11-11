<?php

namespace App\Entity;

interface AeosEntityInterface
{
    public function setAeosId(string $aeosId): object;

    public function getAeosId(): ?string;

    public function setAeosData(array $aeosData): object;

    public function getAeosData(): ?array;
}
