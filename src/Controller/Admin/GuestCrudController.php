<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Controller\Admin;

use App\Entity\Guest;
use App\Entity\Role;
use App\Form\TimeRangesType;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Translation\TranslatableMessage;

class GuestCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Guest::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->addFormTheme('admin/form/form.html.twig');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->disable(Action::DELETE);
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters
    ): QueryBuilder {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $queryBuilder->andWhere(sprintf('%s.expiredAt is null', $queryBuilder->getRootAliases()[0]));

        return $queryBuilder;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', new TranslatableMessage('Name'));
        yield TextField::new('company', new TranslatableMessage('Company'));
        yield BooleanField::new('enabled', new TranslatableMessage('Enabled'))
            ->renderAsSwitch(false)
            ->hideWhenCreating();
        yield TextField::new('phone', new TranslatableMessage('Phone'));
        yield EmailField::new('email', new TranslatableMessage('Email'));
        yield AssociationField::new('templates')
            ->setTemplatePath('admin/templates.html.twig')
            ->setFormTypeOptions([
                'expanded' => true,
                'required' => true,
            ]);
        yield DateField::new('startTime', new TranslatableMessage('Start date'));
        yield DateField::new('endTime', new TranslatableMessage('End date'));
        yield CodeEditorField::new('timeRanges', new TranslatableMessage('Access times'))
            ->hideOnIndex()
            ->setFormType(TimeRangesType::class)
        ;
        yield DateTimeField::new('createdAt', new TranslatableMessage('Created at'))
            ->onlyOnIndex();
        yield DateTimeField::new('activatedAt', new TranslatableMessage('Activated at'))
            ->onlyOnIndex();
        yield AssociationField::new('createdBy', new TranslatableMessage('Created by'))
            ->onlyOnIndex()
            ->setPermission(Role::ADMIN->name);
    }
}
