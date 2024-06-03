<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Controller\Admin;

use App\Entity\Role;
use App\Entity\Template;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Translation\TranslatableMessage;

class TemplateCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Template::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->setEntityPermission(Role::TEMPLATE_ADMIN->value);
    }

    public function configureAssets(Assets $assets): Assets
    {
        return parent::configureAssets($assets)
            // @see https://www.jsdelivr.com/package/npm/tom-select?path=dist
            ->addJsFile('https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js')
            ->addCssFile('https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css');
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->disable(Action::DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name', new TranslatableMessage('Name'));
        yield TextField::new('level', new TranslatableMessage('Level'));
        yield BooleanField::new('enabled', new TranslatableMessage('Enabled'))
            ->renderAsSwitch(false);
        yield TextField::new('aeosId', new TranslatableMessage('AEOS id'))
            // The aeosId field is required on Template, but due to our Tom
            // Select overlay we make the field not required and let the
            // validator report any problems.
            ->setFormTypeOptions([
                'required' => false,
                'label_attr' => [
                    'class' => 'required',
                ],
            ]);
        yield DateTimeField::new('createdAt', new TranslatableMessage('Created at'))
            ->setTimezone($this->getParameter('view_timezone'))
            ->onlyOnIndex();
        yield AssociationField::new('createdBy', new TranslatableMessage('Created by'))
            ->onlyOnIndex();
        yield DateTimeField::new('updatedAt', new TranslatableMessage('Updated at'))
            ->setTimezone($this->getParameter('view_timezone'))
            ->onlyOnIndex();
        yield AssociationField::new('updatedBy', new TranslatableMessage('Updated by'))
            ->onlyOnIndex();
    }
}
