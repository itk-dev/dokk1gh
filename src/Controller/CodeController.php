<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2020 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Controller;

use App\Entity\Code;
use App\Service\AeosHelper;
use App\Service\TemplateManager;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class CodeController extends AdminController
{
    /** @var AeosHelper */
    protected $aeosHelper;

    /** @var array */
    protected $options;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        TemplateManager $templateManager,
        Environment $twig,
        TranslatorInterface $translator,
        AeosHelper $aeosHelper,
        array $codeControllerOptions
    ) {
        parent::__construct($tokenStorage, $templateManager, $twig, $translator);
        $this->aeosHelper = $aeosHelper;

        $resolver = new OptionsResolver();
        $this->configureOptions($resolver);

        $this->options = $resolver->resolve($codeControllerOptions);
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
        $startTime = new \DateTime($this->options['code.defaults.startTime'], $timeZone);
        $endTime = new \DateTime($this->options['code.defaults.endTime'], $timeZone);
        $code->setStartTime($startTime)
            ->setEndTime($endTime);

        return $code;
    }

    private function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['code.defaults.startTime', 'code.defaults.endTime']);
    }
}
