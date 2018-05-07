<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2018 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Service\TemplateManager;
use AppBundle\Service\UserManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserController extends AdminController
{
    private $userManager;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        TemplateManager $templateManager,
        \Twig_Environment $twig,
        UserManager $userManager
    ) {
        parent::__construct($tokenStorage, $templateManager, $twig);
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

    public function notifyUserCreatedAction()
    {
        $id = $this->request->query->get('id');
        $easyadmin = $this->request->attributes->get('easyadmin');
        $user = $easyadmin['item'];

        $this->userManager->notifyUserCreated($user, true);
        $this->showInfo('User %user% notified', ['%user%' => $user]);

        $refererUrl = $this->request->query->get('referer');

        return $refererUrl ? $this->redirect(urldecode($refererUrl))
            : $this->redirectToRoute('easyadmin', ['action' => 'edit', 'entity' => 'User', 'id' => $user->getId()]);
    }

    public function resetPasswordAction()
    {
        $id = $this->request->query->get('id');
        $easyadmin = $this->request->attributes->get('easyadmin');
        $user = $easyadmin['item'];

        $this->userManager->resetPassword($user, true);
        $this->showInfo('Password for %user% reset', ['%user%' => $user]);

        $refererUrl = $this->request->query->get('referer');

        return $refererUrl ? $this->redirect(urldecode($refererUrl))
            : $this->redirectToRoute('easyadmin', ['action' => 'edit', 'entity' => 'User', 'id' => $user->getId()]);
    }

    /**
     * Custom User form builder to make sure that only some templates are available.
     *
     * @param \AppBundle\Entity\User $user
     * @param $view
     *
     * @return \Symfony\Component\Form\FormBuilder
     */
    protected function createUserEntityFormBuilder(User $user, $view)
    {
        $builder = parent::createEntityFormBuilder($user, $view);
        if ($builder->has('templates')) {
            $field = $builder->get('templates');
            $options = $field->getOptions();
            $options['choices'] = $this->templateManager->getTemplates();
            // We have to unset the "choice_loader" to make "choices" work.
            unset($options['choice_loader']);
            // Replace the field (see https://stackoverflow.com/a/14699235).
            $builder->add($field->getName(), EntityType::class, $options);
        }

        return $builder;
    }

    /**
     * Generate a random string.
     *
     * @param mixed $length
     *
     * @return string
     */
    private function generateApiKey($length = 30)
    {
        return base64_encode(random_bytes($length));
    }
}
