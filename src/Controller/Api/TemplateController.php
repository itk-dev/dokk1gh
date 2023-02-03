<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2020 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Controller\Api;

use App\Service\TemplateManager;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;

/**
 * Class TemplateController.
 *
 * @Rest\Route("/api/templates", name="api_template_")
 * @Rest\View(serializerGroups={"api"})
 */
class TemplateController extends AbstractFOSRestController
{
    private $templateManager;

    public function __construct(TemplateManager $templateManager)
    {
        $this->templateManager = $templateManager;
    }

    /**
     * @Rest\Get("", name="cget")
     *
     * @SWG\Tag(name="Template")
     * @SWG\Response(
     *  response=200,
     *  description="List of templates",
     *  @SWG\Schema(
     *    type="array",
     *    @SWG\Items(type="object")
     *  )
     * )
     */
    public function cgetAction()
    {
        return $this->templateManager->getUserTemplates();
    }
}
