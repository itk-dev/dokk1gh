<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Code;
use AppBundle\Service\AeosHelper;
use AppBundle\Service\TemplateManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CodeController extends AdminController
{
    /** @var AeosHelper */
    protected $aeosHelper;

    /** @var \Symfony\Component\DependencyInjection\ContainerInterface */
    protected $container;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        TemplateManager $templateManager,
        \Twig_Environment $twig,
        AeosHelper $aeosHelper,
        ContainerInterface $container
    ) {
        parent::__construct($tokenStorage, $templateManager, $twig);
        $this->aeosHelper = $aeosHelper;
        $this->container = $container;
    }

    protected function listAction()
    {
        // EasyAdmin injects default sorting (by id), so we cannot check for
        // "sortField" using $this->request->query->has().
        parse_str($this->request->getQueryString(), $query);
        if (!isset($query['sortField'])) {
            $this->request->query->add([
                'sortField' => 'status',
            ]);
        }

        return parent::listAction();
    }

    protected function createSearchQueryBuilder(
        $entityClass,
        $searchQuery,
        array $searchableFields,
        $sortField = null,
        $sortDirection = null,
        $dqlFilter = null
    ) {
        $sortByStatus = false;
        if ('status' === $sortField) {
            $sortField = null;
            $sortByStatus = true;
        }
        $builder = parent::createSearchQueryBuilder(
            $entityClass,
            $searchQuery,
            $searchableFields,
            $sortField,
            $sortDirection,
            $dqlFilter
        );
        if ($sortByStatus) {
            $alias = $builder->getRootAliases()[0];
            // Sort by code "status"
            //   1. active: startTime <= now <= endTime
            //   2. future: startTime > now
            //   3. expired: otherwise
            //
            // @see https://stackoverflow.com/a/15269307
            // @see http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/dql-doctrine-query-language.html
            $builder->addSelect('CASE
                                   WHEN CURRENT_TIMESTAMP() BETWEEN '.$alias.'.startTime AND '.$alias.'.endTime THEN 0
                                   WHEN '.$alias.'.startTime > CURRENT_TIMESTAMP() THEN -1
                                   ELSE -2
                                 END HIDDEN sortValue');
            $builder->addOrderBy('sortValue', $sortDirection);
        }

        return $builder;
    }

    protected function createCodeListQueryBuilder($entityClass, $sortDirection, $sortField, $dqlFilter)
    {
        $sortByStatus = false;
        if ('status' === $sortField) {
            $sortField = null;
            $sortByStatus = true;
        }
        $builder = parent::createListQueryBuilder($entityClass, $sortDirection, $sortField, $dqlFilter);

        if ($sortByStatus) {
            $alias = $builder->getRootAliases()[0];
            // Sort by code "status"
            //   1. active: startTime <= now <= endTime
            //   2. future: startTime > now
            //   3. expired: otherwise
            //
            // @see https://stackoverflow.com/a/15269307
            // @see http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/dql-doctrine-query-language.html
            $builder->addSelect('CASE
                                   WHEN CURRENT_TIMESTAMP() BETWEEN '.$alias.'.startTime AND '.$alias.'.endTime THEN 0
                                   WHEN '.$alias.'.startTime > CURRENT_TIMESTAMP() THEN -1
                                   ELSE -2
                                 END HIDDEN sortValue');
            $builder->addOrderBy('sortValue', $sortDirection);
        }

        return $builder;
    }

    /**
     * Custom Code form builder to make sure that only some templates are available.
     *
     * @param \AppBundle\Entity\Code $code
     * @param $view
     *
     * @return \Symfony\Component\Form\FormBuilder
     */
    protected function createCodeEntityFormBuilder(Code $code, $view)
    {
        $builder = parent::createEntityFormBuilder($code, $view);
        if ($builder->has('template')) {
            $field = $builder->get('template');
            $options = $field->getOptions();
            // The options should include the currently selected template (if any).
            $options['choices'] = $this->templateManager->getUserTemplates($code->getTemplate());
            // We have to unset the "choice_loader" to make "choices" work.
            unset($options['choice_loader']);
            // Replace the "template" field (see https://stackoverflow.com/a/14699235).
            $builder->add($field->getName(), EntityType::class, $options);
        }

        return $builder;
    }

    protected function createNewCodeEntity()
    {
        if (!$this->aeosHelper->userHasAeosId()) {
            throw new BadRequestHttpException('User has invalid Aeos id.');
        }

        $code = new Code();

        $timeZone = new \DateTimeZone('UTC');
        $startTime = new \DateTime($this->container->getParameter('code.defaults.startTime'), $timeZone);
        $endTime = new \DateTime($this->container->getParameter('code.defaults.endTime'), $timeZone);

        $code->setStartTime($startTime)
            ->setEndTime($endTime);

        return $code;
    }

    protected function prePersistCodeEntity(Code $code)
    {
        $this->createAeosIdentifier($code);
    }

    protected function preUpdateCodeEntity(Code $code)
    {
        if (null === $code->getIdentifier()) {
            $this->createAeosIdentifier($code);
        }
    }

    protected function preRemoveCodeEntity(Code $code)
    {
        if (null !== $code->getIdentifier()) {
            $this->removeAeosIdentifier($code);
        }
    }

    private function createAeosIdentifier(Code $code)
    {
        try {
            $this->aeosHelper->createAeosIdentifier($code);
            $this->showSuccess('code_created.html.twig', ['code' => $code]);
        } catch (\Exception $ex) {
            $this->showError($ex->getMessage());
        }
    }

    private function removeAeosIdentifier(Code $code)
    {
        try {
            $this->aeosHelper->deleteAeosIdentifier($code);
            $this->showInfo('Code removed');
        } catch (\Exception $ex) {
            $this->showError($ex->getMessage());
        }
    }
}
