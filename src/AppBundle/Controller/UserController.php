<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Service\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserController extends AdminController
{
    private $userManager;

    public function __construct(TokenStorageInterface $tokenStorage, UserManager $userManager, \Twig_Environment $twig)
    {
        parent::__construct($tokenStorage, $twig);
        $this->userManager = $userManager;
    }

    // @see http://symfony.com/doc/current/bundles/EasyAdminBundle/integration/fosuserbundle.html
    public function createNewUserEntity()
    {
        $user = $this->userManager->createUser();

        return $user;
    }

    public function prePersistUserEntity(User $user)
    {
        $this->userManager->updateUser($user, false);
        $this->userManager->notifyUserCreated($user, false);
        $this->showInfo('User %user% notified', ['%user%' => $user]);
    }

    public function preUpdateUserEntity(User $user)
    {
        $this->userManager->updateUser($user, false);
    }

    /**
     * @Route("/user/apikey", name="user_apikey")
     * @Method("GET")
     */
    public function apiKeyGetAction(Request $request)
    {
        return $this->render('User/apikey.html.twig', [
            'user' => $this->getUser(),
        ]);
    }

    /**
     * @Route("/user/apikey", name="user_apikey_generate")
     * @Method("POST")
     */
    public function apiKeyPostAction(Request $request)
    {
        $user = $this->getUser();
        $apiKey = $this->generateApiKey();
        $user->setApiKey($apiKey);
        $em = $this->get('doctrine')->getManager();
        $em->persist($user);
        $em->flush();

        $this->showInfo('Api-key generated');

        return $this->apiKeyGetAction($request);
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

    /**
     * @Route("/user/{id}/notify", name="user_notify")
     * @Method("POST")
     * @param User $user
     */
    public function notifyAction(Request $request, User $user)
    {
        $this->userManager->notifyUserCreated($user, true);
        $this->showInfo('User notified');

        $refererUrl = $request->query->get('referer');

        return $refererUrl ? $this->redirect(urldecode($refererUrl))
            : $this->redirectToRoute('easyadmin', ['action' => 'edit', 'entity' => 'User', 'id' => $user->getId()]);
    }
}
