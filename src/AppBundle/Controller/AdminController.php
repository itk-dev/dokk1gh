<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use JavierEguiluz\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;

class AdminController extends BaseAdminController
{
    // @see http://symfony.com/doc/current/bundles/EasyAdminBundle/integration/fosuserbundle.html
    public function createNewUserEntity()
    {
        return $this->get('fos_user.user_manager')->createUser();
    }

    public function prePersistUserEntity(User $user)
    {
        $user->setUsername($user->getEmail());
        $this->get('fos_user.user_manager')->updateUser($user, false);
    }

    public function preUpdateUserEntity(User $user)
    {
        $user->setUsername($user->getEmail());
        $this->get('fos_user.user_manager')->updateUser($user, false);
    }
}
