<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace ApiBundle\Controller;

use AppBundle\Entity\Code;
use AppBundle\Entity\Template;
use AppBundle\Service\AeosHelper;
use AppBundle\Service\TemplateManager;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Intl\Exception\NotImplementedException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class CodeController.
 *
 * @Rest\View(serializerGroups={"api"})
 */
class CodeController extends Controller implements ClassResourceInterface
{
    /** @var \AppBundle\Service\AeosHelper */
    private $aeosHelper;

    /** @var \AppBundle\Service\TemplateManager */
    private $templateManager;

    /** @var \Doctrine\ORM\EntityManagerInterface */
    private $entityManager;

    /** @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface */
    private $authorizationChecker;

    public function __construct(
        AeosHelper $aeosHelper,
        TemplateManager $templateManager,
        EntityManagerInterface $entityManager,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->aeosHelper = $aeosHelper;
        $this->templateManager = $templateManager;
        $this->entityManager = $entityManager;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * @SWG\Tag(name="Code")
     * @SWG\Response(
     *  response=200,
     *  description="List of codes",
     *  @SWG\Schema(
     *    type="array"
     *  )
     * )
     */
    public function cgetAction(Request $request)
    {
        $user = $this->getUser();
        $criteria = [
            'createdBy' => $user ? $user->getId() : 0,
        ];
        // If "all" is set and user is administrator, list all codes.
        if ($request->query->get('all', false) && $this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            unset($criteria['createdBy']);
        }
        $result = $this->entityManager->getRepository(Code::class)->findBy($criteria);

        return $result;
    }

    /**
     * @SWG\Tag(name="Code")
     * @ SWG\Parameter(name="template", type="integer", description="Template id", in="body")
     * @ SWG\Parameter(name="startTime", type="datetime", description="The start time", in="body")
     * @ SWG\Parameter(name="endTime", type="datetime", description="The end time", in="body")
     * @SWG\Response(
     *  response=201,
     *  description="Code created",
     *  @SWG\Schema(
     *    type="array"
     *  )
     * )
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    public function postAction(Request $request)
    {
        $data = $request->request->all();

        foreach (['template', 'startTime', 'endTime'] as $key) {
            if (!isset($data[$key])) {
                throw new \Exception('Missing data: '.$key);
            }
        }

        $template = $this->templateManager->getUserTemplate($data['template']);
        if (!$template) {
            throw new \Exception('Invalid template: '.$data['template']);
        }
        $startTime = new \DateTime($data['startTime']);
        $endTime = new \DateTime($data['endTime']);

        $code = new Code();
        $code->setTemplate($template)
            ->setStartTime($startTime)
            ->setEndTime($endTime)
            ->setCreatedBy($this->getUser());
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

        return $result;
    }

    /**
     * @SWG\Tag(name="Code")
     * @ SWG\Parameter(name="template", type="integer", description="Template id", in="body")
     * @ SWG\Parameter(name="startTime", type="datetime", description="The start time", in="body")
     * @ SWG\Parameter(name="endTime", type="datetime", description="The end time", in="body")
     * @SWG\Response(
     *  response=204,
     *  description="Code deleted"
     * )
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param mixed                                     $code
     *
     * @return array
     */
    public function deleteAction($code)
    {
        throw new NotImplementedException(__METHOD__);
    }
}
