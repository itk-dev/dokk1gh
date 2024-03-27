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
use App\Entity\Guest;
use App\Entity\Role;
use App\Entity\Setting;
use App\Entity\Template;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Translation\TranslatableMessage;

class DashboardController extends AbstractDashboardController
{
    #[Route('/', name: 'admin')]
    public function index(): Response
    {
        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);

        return $this->redirect($adminUrlGenerator->setController(CodeCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle($this->getParameter('site_name'));
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToCrud(new TranslatableMessage('Code'), 'fas fa-id-card', Code::class);
        yield MenuItem::linkToCrud(new TranslatableMessage('Guest'), 'fas fa-users', Guest::class)
            ->setPermission(Role::GUEST_ADMIN->value)
        ;
        yield MenuItem::linkToCrud(new TranslatableMessage('User'), 'fas fa-user', User::class)
            ->setPermission(Role::USER_ADMIN->value)
        ;
        yield MenuItem::linkToCrud(new TranslatableMessage('Template'), 'fas fa-folder-open', Template::class)
            ->setPermission(Role::TEMPLATE_ADMIN->value)
        ;
        yield MenuItem::linkToCrud(new TranslatableMessage('Settings'), 'fas fa-cog', Setting::class)
           ->setPermission(Role::CONFIG_ADMIN->value)
        ;
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        return parent::configureUserMenu($user)
            ->addMenuItems([
                MenuItem::linkToRoute(new TranslatableMessage('API key'), 'fa fa-key', 'user_apikey'),
            ]);
    }
}
