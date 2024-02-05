<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Entity;

interface AeosEntityInterface
{
    public function setAeosId(string $aeosId);

    public function getAeosId(): ?string;

    public function setAeosData(array $aeosData);

    public function getAeosData(): ?array;
}
