<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2020 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Mock\Controller;

use App\Mock\Entity\AeosActionLogEntry;
use App\Mock\Service\ActionLogManager;
use App\Mock\Service\AeosWebService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/mock/aeosws")
 */
class AeosWebServiceController extends AbstractController
{
    /** @var ActionLogManager */
    private $manager;

    public function __construct(ActionLogManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @Route()
     */
    public function indexAction(AeosWebService $aeosWebService)
    {
        $server = new \SoapServer(__DIR__.'/../Resources/aeosws/wsdl/aeosws.wsdl');
        $server->setObject($aeosWebService);

        $response = new Response();
        $response->headers->set('Content-Type', 'text/xml; charset=UTF-8');

        ob_start();
        $server->handle();
        $response->setContent(ob_get_clean());

        return $response;
    }

    /**
     * @Route("/log", name="aeosws_log")
     */
    public function logAction()
    {
        $items = $this->manager->findAll(AeosActionLogEntry::class);

        return $this->render('@Mock/aeosws/index.html.twig', [
            'items' => $items,
        ]);
    }
}
