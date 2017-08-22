<?php

namespace AppBundle\Mock\Controller;

use AppBundle\Mock\Service\AeosWebService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/mock/")
 */
class AeosWebServiceController extends Controller
{
    /**
     * @Route("aeosws")
     */
    public function aeoswsAction(AeosWebService $aeosWebService)
    {
        $server = new \SoapServer(__DIR__.'/wsdl/aeosws.wsdl');
        $server->setObject($aeosWebService);

        $response = new Response();
        $response->headers->set('Content-Type', 'text/xml; charset=UTF-8');

        ob_start();
        $server->handle();
        $response->setContent(ob_get_clean());

        return $response;
    }
}
