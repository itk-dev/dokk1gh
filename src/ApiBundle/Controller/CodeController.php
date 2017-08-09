<?php

namespace ApiBundle\Controller;

use AppBundle\Entity\Code;
use AppBundle\Entity\Template;
use AppBundle\Service\AeosHelper;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class CodeController extends Controller implements ClassResourceInterface
{
    private $aeosHelper;
    private $entityManager;

    public function __construct(AeosHelper $aeosHelper, EntityManagerInterface $entityManager)
    {
        $this->aeosHelper = $aeosHelper;
        $this->entityManager = $entityManager;
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
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return array
     */
    public function postAction(Request $request)
    {
        $data = $request->request->all();

        $user = $this->getUser();
        $template = $this->entityManager->getRepository(Template::class)->find($data['template']);
        $startTime = new \DateTime($data['startTime']);
        $endTime = new \DateTime($data['endTime']);

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

        return $result;
    }
}
