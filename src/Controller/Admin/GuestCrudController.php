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
use App\Service\GuestService;
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
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Translation\TranslatableMessage;

class GuestCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly GuestService $guestService
    ) {
    }

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
        $actions = parent::configureActions($actions)
            ->disable(Action::DELETE)
            ->add(
                Crud::PAGE_INDEX,
                Action::new('showApp', new TranslatableMessage('Show app'))
                    ->linkToCrudAction('showApp')
            );

        // @todo This should only be added if app has not already been sent.
        // $actions->add(Crud::PAGE_INDEX,
        //     Action::new('sendApp', new TranslatableMessage('Send app'))
        //         ->linkToCrudAction('sendApp')
        // );

        // @todo This should only be added if app has already been sent.
        // $actions->add(Crud::PAGE_INDEX,
        //     Action::new('resendApp', new TranslatableMessage('Resend app'))
        //         ->linkToCrudAction('resendApp')
        // );

        // @todo Ask for confirmation before expiring an app
        // $actions->add(Crud::PAGE_INDEX,
        //     Action::new('expireApp', new TranslatableMessage('Expire app'))
        //         ->linkToCrudAction('expireApp')
        // );

        return $actions;
    }

    public function showApp()
    {
        $guest = $this->getGuest();

        return $this->redirectToRoute('app_code', ['guest' => $guest->getId()]);
    }

    public function sendApp()
    {
        $guest = $this->getGuest();
        if (null !== $guest) {
            $appUrl = $this->generateUrl('app_code', ['guest' => $guest->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
            if ($this->guestService->sendApp($guest, $appUrl)) {
                $this->addFlash('info', 'App sent');
            }
        }

        return $this->redirect(
            $this->container->get(AdminUrlGenerator::class)->setAction(Action::INDEX)->generateUrl()
        );
    }

    public function resendApp()
    {
        return $this->sendApp();
    }

    public function expireApp()
    {
        $guest = $this->getGuest();
        if (null !== $guest) {
            if ($this->guestService->expire($guest)) {
                $this->addFlash('info', 'Guest '.$guest->getId().' expired');
            }
        }

        return $this->redirect(
            $this->container->get(AdminUrlGenerator::class)->setAction(Action::INDEX)->generateUrl()
        );
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

    public function createEntity(string $entityFqcn)
    {
        return $this->guestService->createNewGuest();
    }

    private function getGuest(): Guest
    {
        return $this->getContext()->getEntity()->getInstance();
    }
}