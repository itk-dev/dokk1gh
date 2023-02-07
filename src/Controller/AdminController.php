<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2020 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Controller;

use App\Service\TemplateManager;
use EasyCorp\Bundle\EasyAdminBundle\Controller\EasyAdminController;
use Gedmo\Blameable\Blameable;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class AdminController extends EasyAdminController
{
    /** @var \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface */
    protected $tokenStorage;

    /** @var \App\Service\TemplateManager */
    protected $templateManager;

    /** @var Environment */
    protected $twig;

    /** @var TranslatorInterface */
    protected $translator;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        TemplateManager $templateManager,
        Environment $twig,
        TranslatorInterface $translator
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->templateManager = $templateManager;
        $this->twig = $twig;
        $this->translator = $translator;
    }

    protected function createListQueryBuilder($entityClass, $sortDirection, $sortField = null, $dqlFilter = null)
    {
        $this->limitByUser($dqlFilter, $entityClass, 'entity');

        return parent::createListQueryBuilder($entityClass, $sortDirection, $sortField, $dqlFilter);
    }

    protected function createSearchQueryBuilder(
        $entityClass,
        $searchQuery,
        array $searchableFields,
        $sortField = null,
        $sortDirection = null,
        $dqlFilter = null
    ) {
        // Use only list fields in search query.
        $this->entity['search']['fields'] = array_filter($this->entity['search']['fields'], function ($key) {
            return isset($this->entity['list']['fields'][$key]);
        }, \ARRAY_FILTER_USE_KEY);

        $this->limitByUser($dqlFilter, $entityClass, 'entity');

        return parent::createSearchQueryBuilder(
            $entityClass,
            $searchQuery,
            $searchableFields,
            $sortField,
            $sortDirection,
            $dqlFilter
        );
    }

    protected function showSuccess(string $message, array $parameters = [])
    {
        $this->showMessage('success', $message, $parameters);
    }

    protected function showInfo(string $message, array $parameters = [])
    {
        $this->showMessage('info', $message, $parameters);
    }

    protected function showWarning(string $message, array $parameters = [])
    {
        $this->showMessage('warning', $message, $parameters);
    }

    protected function showError(string $message, array $parameters = [])
    {
        $this->showMessage('error', $message, $parameters);
    }

    protected function showMessage(string $type, string $message, array $parameters = [])
    {
        // If message looks like a twig template filename we render it as a template.
        if (preg_match('/\.(html|txt)\.twig$/', $message)) {
            $message = $this->twig->render($message, $parameters);
            $parameters = [];
        } else {
            $message = $this->translator->trans($message, $parameters);
        }

        $this->addFlash($type, $message);
    }

    private function limitByUser(string &$dqlFilter = null, string $entityClass, string $alias)
    {
        $limitByUserFilter = $this->getLimitByUserFilter($entityClass, $alias);
        if ($limitByUserFilter) {
            if ($dqlFilter) {
                $dqlFilter .= ' and '.$limitByUserFilter;
            } else {
                $dqlFilter = $limitByUserFilter;
            }
        }
    }

    private function getLimitByUserFilter(string $entityClass, string $alias)
    {
        // instanceof does not work with string as first operand.
        if (!is_subclass_of($entityClass, Blameable::class)) {
            return null;
        }

        $authorizationChecker = $this->get('security.authorization_checker');
        if (!$this->isGranted('ROLE_ADMIN')) {
            $user = $this->getUser();

            return $alias.'.createdBy = '.($user ? $user->getId() : -1);
        }

        return null;
    }
}
