<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2020 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Controller\Api\Admin;

use App\Entity\Template;
use App\Entity\User;
use App\Service\AeosService;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Rest\Route("/api/admin", name="api_admin_")
 * @ Rest\Prefix("admin")
 * @ Rest\NamePrefix("admin_")
 */
class AdminController extends AbstractFOSRestController
{
    private $aeosService;

    public function __construct(AeosService $aeosService)
    {
        $this->aeosService = $aeosService;
    }

    /**
     * @Rest\Get("/units", name="get_units")
     * @SWG\Tag(name="Unit")
     * @SWG\Response(
     *  response=200,
     *  description="List of units",
     *  @SWG\Schema(
     *    type="array",
     *    @SWG\Items(type="string")
     *  )
     * )
     *
     * @return null|array
     */
    public function getUnitsAction(Request $request)
    {
        $result = $this->aeosService->getUnits($request->query->all());

        return $result;
    }

    /**
     * @Rest\Get("/people", name="get_people")
     *
     * @SWG\Tag(name="Person")
     * @SWG\Response(
     *   response=200,
     *   description="List of persons",
     *   @SWG\Schema(
     *     type="array",
     *    @SWG\Items(type="string")
     *   )
     * )
     *
     * @return null|array
     */
    public function getPeopleAction(Request $request)
    {
        $result = $this->aeosService->getPersons($request->query->all());

        if ($result) {
            $result = array_map(static fn ($item) => (array) $item, $result);
        }

        return $result;
    }

    /**
     * @Rest\Get("/people/search", name="get_people_search")
     *
     * @SWG\Tag(name="Person")
     * @SWG\Parameter(name="query", type="string", description="The query", in="query"),
     * @SWG\Response(
     *   response=200,
     *   description="Find people",
     *   @SWG\Schema(
     *     type="array",
     *    @SWG\Items(type="string")
     *   )
     * )
     */
    public function getPeopleSearchAction(Request $request)
    {
        $data = $this->searchAction($request, 'getPersons', ['Id', 'PersonnelNo', 'LastName', 'FirstName'], User::class);

        $data = array_map(static fn ($item) => (array) $item, $data ?? []);

        return $data;
    }

    /**
     * @Rest\Get("/people/{id}", name="get_people_show")
     *
     * @SWG\Tag(name="Person")
     * @SWG\Response(
     *   response=200,
     *   description="Show details of a person",
     *   @SWG\Schema(
     *     type="array",
     *    @SWG\Items(type="string")
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
     * @Rest\Get("/templates", name="get_templates")
     *
     * @SWG\Tag(name="Template")
     * @SWG\Response(
     *  response=200,
     *  description="List of templates",
     *  @SWG\Schema(
     *    type="array",
     *    @SWG\Items(type="string")
     *  )
     * )
     *
     * @return null|array
     */
    public function getTemplatesAction(Request $request)
    {
        $data = $this->aeosService->getTemplates($request->query->all());

        $data = array_map(static fn ($item) => (array) $item, $data ?? []);

        return $data;
    }

    /**
     * @Rest\Get("/templates/search", name="get_templates_search")
     *
     * @SWG\Tag(name="Template")
     * @SWG\Parameter(name="query", type="string", description="The query", in="query"),
     * @SWG\Response(
     *   response=200,
     *   description="Find templates",
     *   @SWG\Schema(
     *     type="array",
     *     @SWG\Items(type="object")
     *   )
     * )
     */
    public function getTemplatesSearchAction(Request $request)
    {
        $data = $this->searchAction($request, 'getTemplates', ['Id', 'Name'], Template::class);

        $data = array_map(static fn ($item) => (array) $item, $data ?? []);

        return $data;
    }

    /**
     * @Rest\Get("/template/{id}", name="get_template_show")
     *
     * @SWG\Tag(name="Template")
     * @SWG\Response(
     *   response=200,
     *   description="Show details of a template",
     *   @SWG\Schema(
     *     type="array",
     *    @SWG\Items(type="string")
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
     *    type="array",
     *    @SWG\Items(type="string")
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

    /**
     * @SWG\Tag(name="Miscellaneous")
     * @SWG\Response(
     *  response=200,
     *  description="List of visits",
     *  @SWG\Schema(
     *    type="array",
     *    @SWG\Items(type="string")
     *  )
     * )
     *
     * @return array
     */
    public function getVisitsAction(Request $request)
    {
        $result = $this->aeosService->getVisits($request->query->all());

        return $result;
    }

    /**
     * @SWG\Tag(name="Miscellaneous")
     * @SWG\Response(
     *  response=200,
     *  description="List of visitors",
     *  @SWG\Schema(
     *    type="array",
     *    @SWG\Items(type="string")
     *  )
     * )
     *
     * @return array
     */
    public function getVisitorsAction(Request $request)
    {
        $result = $this->aeosService->getVisitors($request->query->all());

        return $result;
    }

    /**
     * @SWG\Tag(name="Miscellaneous")
     * @SWG\Response(
     *  response=200,
     *  description="List of identifiers",
     *  @SWG\Schema(
     *    type="array",
     *    @SWG\Items(type="string")
     *  )
     * )
     *
     * @return array
     */
    public function getIdentifiersAction(Request $request)
    {
        $result = $this->aeosService->getIdentifiers($request->query->all());

        return $result;
    }

    private function searchAction(Request $request, string $method, array $keys, $class)
    {
        $query = $request->query->get('query');

        if (!$query) {
            return null;
        }

        $result = [];

        // Search for each word in query and intersect results.
        $words = preg_split('/\s+/', $query);
        foreach ($words as $word) {
            $partialResult = [];
            // Merge search results for multiple fields.
            foreach ($keys as $key) {
                $items = $this->aeosService->{$method}([$key => $word]);
                if ($items) {
                    foreach ($items as $item) {
                        $partialResult[$item->Id] = $item;
                    }
                }
            }
            // Ignore empty partial results.
            if ($partialResult) {
                $result = $result ? array_intersect_key($result, $partialResult) : $partialResult;
            }
        }

        $result = array_values($result);

        if (User::class === $class || Template::class === $class) {
            $dql = 'SELECT e.'.(User::class === $class ? 'email' : 'name').' name, e.aeosId
                    FROM '.$class.' e
                    WHERE e.aeosId IS NOT NULL';
            $query = $this->getDoctrine()->getManager()->createQuery($dql);
            $aeosIdsInUse = [];
            foreach ($query->getResult() as $row) {
                $aeosIdsInUse[$row['aeosId']] = $row['name'];
            }

            foreach ($result as $item) {
                $item->_usedBy = isset($aeosIdsInUse[$item->Id]) ? $aeosIdsInUse[$item->Id] : null;
            }
        }

        return $result;
    }
}
