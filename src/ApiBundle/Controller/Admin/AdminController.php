<?php

namespace ApiBundle\Controller\Admin;

use AppBundle\Service\AeosService;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Rest\Prefix("admin")
 * @Rest\NamePrefix("admin_")
 */
class AdminController extends Controller
{
    private $aeosService;

    public function __construct(AeosService $aeosService)
    {
        $this->aeosService = $aeosService;
    }

    /**
     * @SWG\Tag(name="Unit")
     * @SWG\Response(
     *  response=200,
     *  description="List of units",
     *  @ SWG\Schema(
     *    type="array"
     *  )
     * )
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return null|array
     */
    public function getUnitsAction(Request $request)
    {
        $result = $this->aeosService->getUnits($request->query->all());

        return $result;
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
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return null|array
     */
    public function getPeopleAction(Request $request)
    {
        $result = $this->aeosService->getPersons($request->query->all());

        return $result;
    }

    /**
     * @SWG\Tag(name="Person")
     * @SWG\Parameter(name="query", type="string", description="The query", in="query"),
     * @SWG\Response(
     *   response=200,
     *   description="Find people",
     *   @SWG\Schema(
     *     type="array"
     *   )
     * )
     */
    public function getPeopleSearchAction(Request $request)
    {
        return $this->searchAction($request, 'getPersons', ['PersonnelNo', 'LastName', 'FirstName']);
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
     *
     * @param $id
     *
     * @return null|mixed
     */
    public function getPersonAction($id)
    {
        $result = $this->aeosService->getPerson($id);

        return $result;
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
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return null|array
     */
    public function getTemplatesAction(Request $request)
    {
        $result = $this->aeosService->getTemplates($request->query->all());

        return $result;
    }

    /**
     * @SWG\Tag(name="Template")
     * @SWG\Parameter(name="query", type="string", description="The query", in="query"),
     * @SWG\Response(
     *   response=200,
     *   description="Find templates",
     *   @SWG\Schema(
     *     type="array"
     *   )
     * )
     */
    public function getTemplatesSearchAction(Request $request)
    {
        return $this->searchAction($request, 'getTemplates', ['Name']);
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
     *
     * @param $id
     *
     * @return null|mixed
     */
    public function getTemplateAction($id)
    {
        $result = $this->aeosService->getTemplate($id);

        return $result;
    }

    /**
     * @SWG\Tag(name="Code")
     * @SWG\Parameter(name="code", type="string", description="The code", in="path"),
     * @SWG\Response(
     *  response=200,
     *  description="Details on code",
     *  @SWG\Schema(
     *    type="array"
     *  )
     * )
     *
     * @param $code
     *
     * @return array
     */
    public function getCodeAction($code)
    {
        $identifier = $this->aeosService->getIdentifierByBadgeNumber($code);
        $visitor = $identifier ? $this->aeosService->getVisitorByIdentifier($identifier) : null;
        $visit = $visitor ? $this->aeosService->getVisitByVisitor($visitor) : null;

        $result = [
            'identifier' => $identifier,
            'visitor' => $visitor,
            'visit' => $visit,
        ];

        return $result;
    }

    private function searchAction(Request $request, string $method, array $keys)
    {
        $query = $request->query->get('query');

        if (!$query) {
            return null;
        }

        $result = [];

        // Search for each word in query and intersect results.
        $words = preg_split('/\s+/', preg_replace('/[^a-z0-9\s]/i', '', $query));
        foreach ($words as $word) {
            $partResult = [];
            // Merge search results for multiple fields.
            foreach ($keys as $key) {
                $people = $this->aeosService->{$method}([$key => $word]);
                if ($people) {
                    foreach ($people as $person) {
                        $partResult[$person->Id] = $person;
                    }
                }
            }

            $result = $result ? array_intersect_key($result, $partResult) : $partResult;
        }

        return array_values($result);
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
