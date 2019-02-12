<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AppBundle\Controller;

use AppBundle\Service\Configuration;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class CmsController.
 *
 * @Route("/cms")
 */
class CmsController extends Controller
{
    private $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @Route("/gdpr", name="cms_gdpr")
     */
    public function adminGdprAction()
    {
        return $this->render('cms/page.html.twig', [
           'content' => $this->configuration->get('admin_gdpr'),
        ]);
    }
}
