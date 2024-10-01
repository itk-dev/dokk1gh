<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Controller\Admin;

use App\Entity\Code;
use App\Entity\Role;
use App\Service\AeosHelper;
use App\Service\TemplateManager;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\SortOrder;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Translation\TranslatableMessage;

class CodeCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly AeosHelper $aeosHelper,
        private readonly TemplateManager $templateManager,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Code::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityPermission(Role::USER->value)
            ->overrideTemplates([
                'crud/index' => 'admin/Code/list.html.twig',
                'crud/new' => 'admin/Code/new.html.twig',
            ])
            ->setDefaultSort([
                'status' => SortOrder::DESC,
            ]);
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions = parent::configureActions($actions)
            ->disable(Action::EDIT)
        ;

        if (!$this->aeosHelper->userHasAeosId()) {
            $actions->disable(Action::NEW);
        }

        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('identifier', new TranslatableMessage('Code'))
            ->onlyOnIndex()
            ->setTemplatePath('admin/Code/code.html.twig');

        yield TextField::new('status', new TranslatableMessage('Status'))
            ->onlyOnIndex()
            ->setTemplatePath('admin/Code/status.html.twig')
            ->setSortable(true);

        if (Crud::PAGE_INDEX === $pageName) {
            yield DateTimeField::new('startTime', new TranslatableMessage('Time range'))
                ->setTemplatePath('admin/Code/date_time_range.html.twig');
        } else {
            yield DateTimeField::new('startTime', new TranslatableMessage('Date'));
            yield DateTimeField::new('endTime', new TranslatableMessage('Time range'));
        }

        yield AssociationField::new('template', new TranslatableMessage('Template'))
            ->setFormTypeOptions([
                'placeholder' => new TranslatableMessage('Select template'),
                'choices' => $this->templateManager->getUserTemplates(),
            ]);

        yield TextareaField::new('note', new TranslatableMessage('Note'))
            ->setTemplatePath('admin/Code/note.html.twig');

        yield DateTimeField::new('createdAt', new TranslatableMessage('Created at'))
            ->setTimezone($this->getParameter('view_timezone'))
            ->onlyOnIndex();

        yield AssociationField::new('createdBy', new TranslatableMessage('Created by'))
            ->onlyOnIndex()
            ->setPermission(Role::ADMIN->value);
    }

    /**
     * Override index query builder to handle custom sorting on the virtual "status" field.
     */
    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters,
    ): QueryBuilder {
        $sort = $searchDto->getSort();
        if (!\array_key_exists('status', $sort)) {
            return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        }

        $order = $sort['status'];
        unset($sort['status']);

        // Create a new search DTO without the "status" sort.
        $searchDto = new SearchDto(
            $searchDto->getRequest(),
            $searchDto->getSearchableProperties(),
            $searchDto->getQuery(),
            [],
            $sort,
            $searchDto->getAppliedFilters(),
        );

        $queryBuilder = parent::createIndexQueryBuilder(
            $searchDto,
            $entityDto,
            $fields,
            $filters
        );

        // Add our custom sorting.
        $alias = $queryBuilder->getRootAliases()[0];
        // Sort by code "status"
        //   1. active: startTime <= now <= endTime
        //   2. future: startTime > now
        //   3. expired: otherwise
        //
        // @see https://stackoverflow.com/a/15269307
        // @see http://docs.doctrine-project.org/projects/doctrine-orm/en/latest/reference/dql-doctrine-query-language.html
        $queryBuilder
            ->addSelect(
                \sprintf(
                    <<<'SQL'
CASE
    WHEN :gh_now BETWEEN %1$s.startTime AND %1$s.endTime THEN 0
    WHEN %1$s.startTime > :gh_now THEN -1
    ELSE -2
END HIDDEN gh_statusSortValue
SQL,
                    $alias
                )
            )
            ->addOrderBy('gh_statusSortValue', $order)
            ->setParameter('gh_now', new \DateTimeImmutable());

        return $queryBuilder;
    }
}
