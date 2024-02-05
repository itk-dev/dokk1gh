<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Controller\Api;

use App\Service\TemplateManager;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class TemplateController.
 *
 * @Rest\Route("/api/templates", name="api_template_")
 *
 * @Rest\View(serializerGroups={"api"})
 */
class TemplateController extends AbstractController
{
    public function __construct(private readonly TemplateManager $templateManager)
    {
    }

    /**
     * @Rest\Get("", name="cget")
     *
     * @SWG\Tag(name="Template")
     *
     * @SWG\Response(
     *  response=200,
     *  description="List of templates",
     *
     *  @SWG\Schema(
     *    type="array",
     *
     *    @SWG\Items(type="object")
     *  )
     * )
     */
    public function cget()
    {
        return $this->templateManager->getUserTemplates();
    }
}
