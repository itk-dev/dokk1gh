<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2020 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Controller;

use App\Service\Configuration;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class CmsController.
 *
 * @Route("/cms")
 */
class CmsController extends AbstractController
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
