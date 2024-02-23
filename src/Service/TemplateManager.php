<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Service;

use App\Entity\Template;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class TemplateManager
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly TokenStorageInterface $tokenStorage
    ) {
    }

    /**
     * Get all enabled templates.
     *
     * @return array|Template[]
     */
    public function getTemplates()
    {
        return $this->entityManager->getRepository(Template::class)->findBy(['enabled' => true]);
    }

    /**
     * Get templates a user is allowed to use.
     *
     * @return Collection|Template[]
     */
    public function getUserTemplates(?Template $default = null): Collection
    {
        $templates = new ArrayCollection();
        if ($default) {
            $templates->add($default);
        }

        // Non-admins can only use specified templates.
        $user = $this->tokenStorage->getToken()->getUser();
        \assert($user instanceof User);
        $userTemplates = $this->authorizationChecker->isGranted('ROLE_ADMIN')
            ? $this->entityManager->getRepository(Template::class)->findAll()
            : $user->getTemplates();

        foreach ($userTemplates as $userTemplate) {
            $templates->add($userTemplate);
        }

        return $templates;
    }

    /**
     * Get a user template by id.
     *
     * @return null|Template
     */
    public function getUserTemplate(int $id)
    {
        $templates = $this->getUserTemplates();

        foreach ($templates as $template) {
            if ($template->getId() === $id) {
                return $template;
            }
        }

        return null;
    }
}
