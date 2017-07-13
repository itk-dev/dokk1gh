<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Code;
use AppBundle\Entity\Template;
use AppBundle\Service\AeosHelper;
use AppBundle\Service\AeosService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
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
    private $aeosHelper;
    private $entityManager;

    public function __construct(AeosService $aeosService, AeosHelper $aeosHelper, EntityManagerInterface $entityManager)
    {
        $this->aeosService = $aeosService;
        $this->aeosHelper = $aeosHelper;
        $this->entityManager = $entityManager;
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
        $result = $this->aeosService->getTemplate($id);

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
        $result = $this->aeosService->getPerson($id);

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

    /**
     * @Route("/visit", name="api_visit_list")
     */
    public function visitListAction(Request $request)
    {
        $result = $this->aeosService->getVisits($request->query->all());

        return new JsonResponse($result);
    }

    /**
     * @Route("/visitor", name="api_visitor_list")
     */
    public function visitorListAction(Request $request)
    {
        $result = $this->aeosService->getVisitors($request->query->all());

        return new JsonResponse($result);
    }

    /**
     * @Route("/identifier", name="api_identifier_list")
     */
    public function identifierListAction(Request $request)
    {
        $result = $this->aeosService->getIdentifiers($request->query->all());

        return new JsonResponse($result);
    }

    /**
     * @Route("/code/new", name="api_code_create")
     * @Method("POST")
     */
    public function codeCreateAction(Request $request)
    {
        $data = json_decode($request->getContent());

        $user = $this->getUser();
        $template = $this->entityManager->getRepository(Template::class)->find($data->template);
        $startTime = new \DateTime($data->startTime);
        $endTime = new \DateTime($data->endTime);

        $code = new Code();
        $code->setTemplate($template)
            ->setStartTime($startTime)
            ->setEndTime($endTime)
            ->setCreatedBy($user);
        $this->aeosHelper->createAeosIdentifier($code);
        $this->entityManager->persist($code);
        $this->entityManager->flush();

        $result = [
            'status' => 'ok',
            'code' => $code->getIdentifier(),
        ];

        return new JsonResponse($result);
    }

    /**
     * @Route("/code/{code}/info", name="api_code_info")
     * @Method("GET")
     */
    public function codeInfoAction(Request $request, Code $code)
    {
        $data = json_decode($request->getContent());

        $user = $this->getUser();
        $template = $this->entityManager->getRepository(Template::class)->find($data->template);
        $startTime = new \DateTime($data->startTime);
        $endTime = new \DateTime($data->endTime);

        $code = new Code();
        $code->setTemplate($template)
            ->setStartTime($startTime)
            ->setEndTime($endTime)
            ->setCreatedBy($user);
        $this->aeosHelper->createAeosIdentifier($code);
        $this->entityManager->persist($code);
        $this->entityManager->flush();

        $result = [
            'status' => 'ok',
            'code' => $code->getIdentifier(),
        ];

        return new JsonResponse($result);
    }
}
