<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Controller\Api\Admin;

use App\Controller\Api\AbstractApiController;
use App\Entity\Template;
use App\Entity\User;
use App\Service\AeosService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/api', name: 'api_admin_')]
class AdminController extends AbstractApiController
{
    public function __construct(
        private readonly AeosService $aeosService,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/people', name: 'get_people')]
    public function getPeople(Request $request): JsonResponse
    {
        return $this->createResponse($this->aeosService->getPersons($request->query->all()));
    }

    #[Route('/people/search', name: 'get_people_search')]
    public function getPeopleSearch(Request $request): Response
    {
        return $this->createResponse(
            $this->searchAction($request, 'getPersons', ['Id', 'PersonnelNo', 'LastName', 'FirstName'], User::class)
        );
    }

    #[Route('/people/{id}', name: 'get_people_show')]
    public function getPerson(string $id): Response
    {
        $result = $this->aeosService->getPerson($id);

        return $this->createResponse($result);
    }

    #[Route('/templates', name: 'get_templates')]
    public function getTemplates(Request $request): Response
    {
        return $this->createResponse(
            $this->aeosService->getTemplates($request->query->all())
        );
    }

    #[Route('/templates/search', name: 'get_templates_search')]
    public function getTemplatesSearch(Request $request): Response
    {
        return $this->createResponse(
            $data = $this->searchAction($request, 'getTemplates', ['Id', 'Name'], Template::class)
        );
    }

    #[Route('/templates/{id}', name: 'get_template_show')]
    public function getTemplate(string $id): Response
    {
        return $this->createResponse(
            $result = $this->aeosService->getTemplate($id)
        );
    }

    private function searchAction(Request $request, string $method, array $keys, string $class): ?array
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
            $query = $this->entityManager->createQuery($dql);
            $aeosIdsInUse = [];
            foreach ($query->getResult() as $row) {
                $aeosIdsInUse[$row['aeosId']] = $row['name'];
            }

            foreach ($result as $item) {
                $item->_usedBy = $aeosIdsInUse[$item->Id] ?? null;
            }
        }

        return $result;
    }
}
