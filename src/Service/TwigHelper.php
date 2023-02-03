<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2020 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Service;

use Twig\Environment;

class TwigHelper
{
    /** @var Environment */
    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
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
