<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Twig\Extension;

use App\Service\AeosHelper;
use App\Service\Configuration;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function __construct(
        private readonly AeosHelper $aeosHelper,
        private readonly Configuration $configuration
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('user_has_aeos_id', $this->aeosHelper->userHasAeosId(...)),
            new TwigFunction('get_configuration', $this->configuration->get(...)),
            new TwigFunction('app_icon', $this->getAppIcon(...)),
        ];
    }

    public function getAppIcon($size)
    {
        return $this->configuration->get('app_icons.'.$size.'x'.$size);
    }
}
