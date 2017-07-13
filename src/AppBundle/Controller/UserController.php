<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class UserController extends Controller
{
    /**
     * @Route("/user/{id}/generate-api-key", name="user_generate_api_key")
     * @Method("POST")
     * @param \AppBundle\Controller\User $user
     */
    public function generateApiKeyAction(Request $request, User $user)
    {
        $apiKey = $this->generateApiKey();
        $user->setApiKey($apiKey);
        $em = $this->get('doctrine')->getManager();
        $em->persist($user);
        $em->flush();

        $refererUrl = $request->query->get('referer');

        return $refererUrl ? $this->redirect(urldecode($refererUrl))
            : $this->redirectToRoute('easyadmin', ['action' => 'show', 'entity' => 'User', 'id' => $user->getId()]);
    }

    /**
     * Generate a random string.
     *
     * @return string
     */
    private function generateApiKey($length = 30)
    {
        return base64_encode(random_bytes($length));
    }
}
