<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as BaseAbstractController;

abstract class AbstractController extends BaseAbstractController
{
    protected function getCurrentUser(): ?User
    {
        $user = parent::getUser();

        \assert(null === $user || $user instanceof User);

        return $user;
    }
}
