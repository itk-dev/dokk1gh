<?php

namespace AppBundle\Twig;

use AppBundle\Service\AeosHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AppExtension extends \Twig_Extension
{
    /** @var \AppBundle\Service\AeosHelper */
    private $aeosHelper;

    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    private $container;

    public function __construct(AeosHelper $aeosHelper, ContainerInterface $container)
    {
        $this->aeosHelper = $aeosHelper;
        $this->container = $container;
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('user_has_aeos_id', [$this->aeosHelper, 'userHasAeosId']),
            new \Twig_SimpleFunction('get_app_parameter', [$this->container, 'getParameter']),
        ];
    }
}
