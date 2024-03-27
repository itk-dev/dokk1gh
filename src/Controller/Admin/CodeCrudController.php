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
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Translation\TranslatableMessage;

class CodeCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly AeosHelper $aeosHelper,
        private readonly TemplateManager $templateManager
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
            ->setTemplatePath('admin/Code/status.html.twig');
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
        yield TextareaField::new('note', new TranslatableMessage('Note'));
        yield DateTimeField::new('createdAt', new TranslatableMessage('Created at'))
            ->setTimezone($this->getParameter('view_timezone'))
            ->onlyOnIndex();
        yield AssociationField::new('createdBy', new TranslatableMessage('Created by'))
            ->onlyOnIndex()
            ->setPermission(Role::ADMIN->value);
    }
}
