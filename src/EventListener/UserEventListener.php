<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\EventListener;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: User::class)]
class UserEventListener
{
    public function prePersist(User $user, PrePersistEventArgs $args)
    {
        if (null === $user->getPassword()) {
            $user->setPassword(sha1(uniqid('', true)));
        }
        $user->setEnabled(true);
    }
}
