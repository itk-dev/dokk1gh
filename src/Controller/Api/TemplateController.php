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
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class TemplateController.
 *
 * @Rest\View(serializerGroups={"api"})
 */
#[Route('/api/templates', name: 'api_template_')]
class TemplateController extends AbstractApiController
{
    public function __construct(
        private readonly TemplateManager $templateManager
    ) {
    }

    #[Route('', name: 'index')]
    public function index(SerializerInterface $serializer): Response
    {
        $templates = $this->templateManager->getUserTemplates();

        return $this->createResponse(
            json_decode(
                $serializer->serialize($templates, 'json', ['groups' => 'api'])
            )
        );
    }
}
