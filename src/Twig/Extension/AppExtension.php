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
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    public function __construct(
        private readonly AeosHelper $aeosHelper,
        private readonly Configuration $configuration
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('url_to_link', $this->urlToLink(...)),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('user_has_aeos_id', $this->aeosHelper->userHasAeosId(...)),
            new TwigFunction('get_configuration', $this->configuration->get(...)),
            new TwigFunction('app_icon', $this->getAppIcon(...)),
        ];
    }

    public function getAppIcon(int $size): string
    {
        return (string) $this->configuration->get('app_icons.'.$size.'x'.$size);
    }

    public function urlToLink(?string $text): ?string
    {
        if (null === $text) {
            return null;
        }

        // @see https://stackoverflow.com/a/47268360
        return preg_replace(
            '/(?<!href="|">)(?<!src=\")((http|ftp)+(s)?:\/\/[^<>\s]+)/is',
            '<a href="\\1" target="_blank">\\1</a>',
            $text
        );
    }
}
