<?php

namespace AppBundle\Controller;

use AppBundle\Service\AeosService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/api")
 */

class ApiController extends Controller
{
    private $aeosService;

    public function __construct(AeosService $aeosService)
    {
        $this->aeosService = $aeosService;
    }

    /**
     * @Route("/", name="api")
     */
    public function indexAction()
    {
        return $this->render('api/index.html.twig');
    }

    /**
     * @Route("/template", name="api_template_list")
     */
    public function templateListAction(Request $request)
    {
        $result = $this->aeosService->getTemplates($request->query->all());

        return new JsonResponse($result);
    }

    /**
     * @Route("/template/{id}", name="api_template_show")
     */
    public function templateShowAction(Request $request, $id)
    {
        $result = $this->aeosService->getTemplates(['Id' => $id]);

        return new JsonResponse($result);
    }

    /**
     * @Route("/person", name="api_person_list")
     */
    public function personListAction(Request $request)
    {
        $result = $this->aeosService->getPersons($request->query->all());

        return new JsonResponse($result);
    }

    /**
     * @Route("/person/{id}", name="api_person_show")
     */
    public function personShowAction(Request $request, $id)
    {
        $result = $this->aeosService->getPersons(['Id' => $id]);

        return new JsonResponse($result);
    }

    /**
     * @Route("/unit", name="api_unit_list")
     */
    public function unitListAction(Request $request)
    {
        $result = $this->aeosService->getUnits($request->query->all());

        return new JsonResponse($result);
    }
}
