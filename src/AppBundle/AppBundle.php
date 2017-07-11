<?php

namespace AppBundle;

use AppBundle\Filter\UserTemplateFilter;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AppBundle extends Bundle
{
    public function boot()
    {
        // Enable filter and inject container.
        // @see http://stackoverflow.com/a/14650403
        $em = $this->container->get('doctrine.orm.default_entity_manager');
        $conf = $em->getConfiguration();
        $conf->addFilter('filter_user_template', UserTemplateFilter::class);
        $em->getFilters()->enable('filter_user_template')->setContainer($this->container);
    }
}
