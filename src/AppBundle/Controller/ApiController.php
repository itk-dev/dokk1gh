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
        $data = [
            $this->generateUrl('api_templates'),
        ];

        return new JsonResponse($data);
    }

    /**
     * @Route("/templates", name="api_templates")
     */
    public function templateAction(Request $request)
    {
        $result = $this->aeosService->getTemplates($request->query->all());

        return new JsonResponse($result);
    }

    /**
     * @Route("/persons", name="api_persons")
     */
    public function personsAction(Request $request)
    {
        $result = $this->aeosService->getPersons($request->query->all());

        return new JsonResponse($result);
    }
}
