<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Service;

use Twig\Environment;

class TwigHelper
{
    public function __construct(private readonly Environment $twig)
    {
    }

    public function renderTemplate($template, $context)
    {
        return $this->twig
            ->createTemplate($template)
            ->render($context);
    }

    public function load($name)
    {
        return $this->twig->load($name);
    }
}
