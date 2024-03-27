<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Controller\Api;

use App\Entity\Code;
use App\Entity\Role;
use App\Service\AeosHelper;
use App\Service\TemplateManager;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class CodeController.
 */
#[Route('/api/codes', name: 'api_code_')]
class CodeController extends AbstractApiController
{
    public function __construct(private readonly AeosHelper $aeosHelper, private readonly TemplateManager $templateManager, private readonly EntityManagerInterface $entityManager, private readonly AuthorizationCheckerInterface $authorizationChecker)
    {
    }

    #[Route('', name: 'index', methods: [Request::METHOD_GET])]
    public function index(Request $request, SerializerInterface $serializer): Response
    {
        $user = $this->getCurrentUser();
        $criteria = [
            'createdBy' => $user ? $user->getId() : 0,
        ];
        // List all codes if query parameter `all` is truthy and user is administrator.
        if (filter_var($request->query->get('all', false), \FILTER_VALIDATE_BOOLEAN)
            && $this->authorizationChecker->isGranted(Role::ADMIN->value)) {
            unset($criteria['createdBy']);
        }

        $codes = $this->entityManager->getRepository(Code::class)->findBy($criteria);

        return $this->createResponse(
            json_decode(
                $serializer->serialize($codes, 'json', ['groups' => 'api'])
            )
        );
    }

    /**
     * @Rest\Post("", name="post")
     *
     * @SWG\Tag(name="Code")
     *
     * @ SWG\Parameter(name="template", type="integer", description="Template id", in="body")
     *
     * @ SWG\Parameter(name="startTime", type="datetime", description="The start time", in="body")
     *
     * @ SWG\Parameter(name="endTime", type="datetime", description="The end time", in="body")
     *
     * @SWG\Response(
     *  response=201,
     *  description="Code created",
     *
     *  @SWG\Schema(
     *    type="array",
     *
     *    @SWG\Items(type="object")
     *  )
     * )
     */
    #[Route('', name: 'create', methods: [Request::METHOD_POST])]
    public function create(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);
        } catch (\Throwable) {
            return $this->createHttpExceptionResponse(
                new BadRequestHttpException('Invalid data')
            );
        }

        foreach (['template', 'startTime', 'endTime'] as $key) {
            if (!isset($data[$key])) {
                return $this->createHttpExceptionResponse(
                    new BadRequestHttpException(sprintf('Missing data: %s', $key))
                );
            }
        }

        $template = $this->templateManager->getUserTemplate((int) $data['template']);
        if (!$template) {
            return $this->createHttpExceptionResponse(
                new BadRequestHttpException(sprintf('Invalid template: %s', $data['template']))
            );
        }
        $startTime = new \DateTime($data['startTime']);
        $endTime = new \DateTime($data['endTime']);

        $code = new Code();
        $code->setTemplate($template)
            ->setStartTime($startTime)
            ->setEndTime($endTime)
            ->setCreatedBy($this->getCurrentUser());
        $this->aeosHelper->createAeosIdentifier($code);
        $this->entityManager->persist($code);
        $this->entityManager->flush();

        $result = [
            'status' => 'ok',
            'code' => $code->getIdentifier(),
            'template' => $template,
            'startTime' => $startTime->format(\DateTime::W3C),
            'endTime' => $endTime->format(\DateTime::W3C),
        ];

        return $this->createResponse($result, Response::HTTP_CREATED);
    }
}
