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
use App\Entity\User;
use App\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Translation\TranslatableMessage;

class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly UserManager $userManager
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return parent::configureCrud($crud)
            ->addFormTheme('admin/form/form.html.twig');
    }

    public function configureActions(Actions $actions): Actions
    {
        return parent::configureActions($actions)
            ->disable(Action::DELETE);
    }

    public function configureAssets(Assets $assets): Assets
    {
        return parent::configureAssets($assets)
            // @see https://www.jsdelivr.com/package/npm/tom-select?path=dist
            ->addJsFile('https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js')
            ->addCssFile('https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css');
    }

    public function createEntity(string $entityFqcn)
    {
        return $this->userManager->createUser();
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::persistEntity($entityManager, $entityInstance);
        \assert($entityInstance instanceof User);
        $this->userManager->notifyUserCreated($entityInstance, false);
        $this->showInfo('User {user} notified', ['user' => $entityInstance->getEmail()]);
    }

    public function configureFields(string $pageName): iterable
    {
        yield EmailField::new('email', new TranslatableMessage('Email'));
        yield BooleanField::new('enabled', new TranslatableMessage('Enabled'))
            ->renderAsSwitch(false);

        $options = array_combine(
            Role::values(),
            Role::values()
        );
        yield ChoiceField::new('roles', new TranslatableMessage('Roles'))
            ->setTemplatePath('admin/User/roles.html.twig')
            ->setFormTypeOptions([
                'multiple' => true,
                'expanded' => true,
                'choices' => $options,
            ])
        ;

        yield AssociationField::new('templates', new TranslatableMessage('Templates'))
            ->setTemplatePath('admin/templates.html.twig')
            ->setFormTypeOptions([
                'expanded' => true,
                'required' => true,
            ])
        ;
        yield TextField::new('aeosId', new TranslatableMessage('AEOS id'));

        yield DateTimeField::new('lastLoggedInAt', new TranslatableMessage('Last logged in at'))
            ->onlyOnIndex();
        yield DateTimeField::new('createdAt', new TranslatableMessage('Created at'))
            ->onlyOnIndex();
        yield AssociationField::new('createdBy', new TranslatableMessage('Created by'))
            ->onlyOnIndex();
        yield DateTimeField::new('updatedAt', new TranslatableMessage('Updated at'))
            ->onlyOnIndex();
        yield AssociationField::new('updatedBy', new TranslatableMessage('Updated by'))
            ->onlyOnIndex();
    }
}
