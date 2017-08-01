<?php

namespace AppBundle\Controller\Api;

use AppBundle\Entity\Template;
use AppBundle\Service\AeosHelper;
use AppBundle\Service\AeosService;
use AppBundle\Service\TemplateManager;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

class TemplateController implements ClassResourceInterface
{
    private $aeosService;
    private $aeosHelper;
    private $entityManager;
    private $templateManager;

    public function __construct(AeosService $aeosService, AeosHelper $aeosHelper, EntityManagerInterface $entityManager, TemplateManager $templateManager)
    {
        $this->aeosService = $aeosService;
        $this->aeosHelper = $aeosHelper;
        $this->entityManager = $entityManager;
        $this->templateManager = $templateManager;
    }

    /**
     * @SWG\Tag(name="Template")
     * @SWG\Response(
     *  response=200,
     *  description="List of templates",
     *  @SWG\Schema(
     *    type="array"
     *  )
     * )
     */
    public function cgetAction(Request $request)
    {
//        $result = $this->aeosService->getTemplates($request->query->all());
        $result = $this->templateManager->getUserTemplates();

        return $result;
    }

    /**
     * @SWG\Tag(name="Template")
     * @SWG\Response(
     *   response=200,
     *   description="Show details of a template",
     *   @SWG\Schema(
     *     type="array"
     *   )
     * )
     */
    public function getAction(Request $request, $id)
    {
        $result = $this->aeosService->getTemplate($id);

        return $result;
    }
}
