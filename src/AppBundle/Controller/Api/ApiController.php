<?php

namespace AppBundle\Controller\Api;

use AppBundle\Service\AeosHelper;
use AppBundle\Service\AeosService;
use Doctrine\ORM\EntityManagerInterface;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

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
    * @SWG\Tag(name="Unit")
    * @SWG\Response(
    *  response=200,
    *  description="List of units",
    *  @SWG\Schema(
    *    type="array"
    *  )
    * )
    */
    public function getUnitsAction(Request $request)
    {
        $result = $this->aeosService->getUnits($request->query->all());

        return $result;
    }

    // /**
    //  * @Rest\Get("/visit", name="api_visit_list")
    //  */
    // public function visitListAction(Request $request)
    // {
    //     $result = $this->aeosService->getVisits($request->query->all());

    //     return new JsonResponse($result);
    // }

    // /**
    //  * @Rest\Get("/visitor", name="api_visitor_list")
    //  */
    // public function visitorListAction(Request $request)
    // {
    //     $result = $this->aeosService->getVisitors($request->query->all());

    //     return new JsonResponse($result);
    // }

    // /**
    //  * @Rest\Get("/identifier", name="api_identifier_list")
    //  */
    // public function identifierListAction(Request $request)
    // {
    //     $result = $this->aeosService->getIdentifiers($request->query->all());

    //     return new JsonResponse($result);
    // }

    // /**
    //  * @Rest\Post("/code/new", name="api_code_create")
    //  */
    // public function codeCreateAction(Request $request)
    // {
    //     $data = json_decode($request->getContent());

    //     $user = $this->getUser();
    //     $template = $this->entityManager->getRepository(Template::class)->find($data->template);
    //     $startTime = new \DateTime($data->startTime);
    //     $endTime = new \DateTime($data->endTime);

    //     $code = new Code();
    //     $code->setTemplate($template)
    //         ->setStartTime($startTime)
    //         ->setEndTime($endTime)
    //         ->setCreatedBy($user);
    //     $this->aeosHelper->createAeosIdentifier($code);
    //     $this->entityManager->persist($code);
    //     $this->entityManager->flush();

    //     $result = [
    //         'status' => 'ok',
    //         'code' => $code->getIdentifier(),
    //     ];

    //     return new JsonResponse($result);
    // }

    // /**
    //  * @Rest\Get("/code/{code}", name="api_code_show")
    //  */
    // public function codeInfoAction(Request $request, $code)
    // {
    //     $identifier = $this->aeosService->getIdentifierByBadgeNumber($code);
    //     $visitor = $identifier ? $this->aeosService->getVisitorByIdentifier($identifier) : null;
    //     $visit = $visitor ? $this->aeosService->getVisitByVisitor($visitor) : null;

    //     $result = [
    //         'identifier' => $identifier,
    //         'visitor' => $visitor,
    //         'visit' => $visit,
    //     ];

    //     return new JsonResponse($result);
    // }
}
