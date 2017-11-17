<?php

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

        if ($path === 'code.config') {
            $config['daysDisabled'] = array_values(array_diff(range(0, 6), isset($config['daysEnabled']) ? $config['daysEnabled'] : []));
        }

        return $config;
    }
}
