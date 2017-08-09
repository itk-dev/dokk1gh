<?php

namespace AppBundle\Service;

use AppBundle\Entity\Template;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class TemplateManager
{
    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var  AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var  TokenStorageInterface */
    protected $tokenStorage;

    public function __construct(EntityManagerInterface $entityManager, AuthorizationCheckerInterface $authorizationChecker, TokenStorageInterface $tokenStorage)
    {
        $this->entityManager = $entityManager;
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Get templates a user is allowed to use.
     *
     * @return ArrayCollection
     */
    public function getUserTemplates(Template $default = null)
    {
        $templates = new ArrayCollection();
        if ($default) {
            $templates->add($default);
        }

        // Non-admins can only use specified templates.
        $userTemplates = $this->authorizationChecker->isGranted('ROLE_ADMIN')
            ? $this->entityManager->getRepository(Template::class)->findAll()
            : $this->tokenStorage->getToken()->getUser()->getTemplates();

        foreach ($userTemplates as $userTemplate) {
            $templates->add($userTemplate);
        }

        return $templates;
    }
}
