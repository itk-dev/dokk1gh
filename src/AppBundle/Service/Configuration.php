<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AppBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;

class Configuration
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function get($path)
    {
        $config = $this->container->getParameter($path);

        if ('code.config' === $path) {
            $config['daysDisabled'] = array_values(
                array_diff(
                    range(0, 6),
                    isset($config['daysEnabled']) ? $config['daysEnabled'] : []
                )
            );
        }

        return $config;
    }
}
