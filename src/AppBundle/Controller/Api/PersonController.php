<?php

namespace AppBundle\Controller\Api;

use AppBundle\Service\AeosHelper;
use AppBundle\Service\AeosService;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

class PersonController implements ClassResourceInterface
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
     * @SWG\Tag(name="Person")
     * @SWG\Response(
     *   response=200,
     *   description="List of persons",
     *   @SWG\Schema(
     *     type="array"
     *   )
     * )
     */
    public function cgetAction(Request $request)
    {
        $result = $this->aeosService->getPersons($request->query->all());

        //return $result;

        return array_map(function ($item) {
            return (array)$item;
        }, $result);
    }

    /**
     * @SWG\Tag(name="Person")
     * @SWG\Response(
     *   response=200,
     *   description="Show details of a person",
     *   @SWG\Schema(
     *     type="array"
     *   )
     * )
     */
    public function getAction(Request $request, $id)
    {
        $result = $this->aeosService->getPerson($id);

        return (array)$result;
    }
}
