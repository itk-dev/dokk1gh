<?php

namespace AppBundle\Controller\Api;

use AppBundle\Service\AeosHelper;
use AppBundle\Service\AeosService;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

class TemplateController implements ClassResourceInterface
{
    private $aeosService;
    private $aeosHelper;
    private $entityManager;

    public function __construct(AeosService $aeosService, AeosHelper $aeosHelper, EntityManagerInterface $entityManager)
    {
        $this->aeosService = $aeosService;
        $this->aeosHelper = $aeosHelper;
        $this->entityManager = $entityManager;
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
        $result = $this->aeosService->getTemplates($request->query->all());

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
