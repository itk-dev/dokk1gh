<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController as BaseAbstractCrudController;
use Symfony\Component\Translation\TranslatableMessage;

abstract class AbstractCrudController extends BaseAbstractCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ->setDateFormat('dd-MM-yyyy')
            ->setTimeFormat('HH:mm:ss')
            ->setDateTimeFormat('dd-MM-yyyy HH:mm:ss')
            ->addFormTheme('admin/form/form.html.twig');
    }

    public function configureAssets(Assets $assets): Assets
    {
        return parent::configureAssets($assets)
            ->addWebpackEncoreEntry('easy_admin');
    }

    protected function showSuccess(string $message, array $parameters = [])
    {
        $this->showMessage('success', $message, $parameters);
    }

    protected function showInfo(string|TranslatableMessage $message, array $parameters = [])
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

    protected function showMessage(string $type, string|TranslatableMessage $message, array $parameters = [])
    {
        $this->addFlash($type, $message);
    }
}
