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

abstract class AbstractCrudController extends BaseAbstractCrudController
{
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->showEntityActionsInlined()
            ->setDateFormat('dd-MM-yyyy')
            ->setTimeFormat('HH:mm:ss')
            ->setDateTimeFormat('dd-MM-yyyy HH:mm:ss');
    }

    public function configureAssets(Assets $assets): Assets
    {
        return parent::configureAssets($assets)
            ->addWebpackEncoreEntry('easy_admin');
    }
}
