<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\EventSubscriber;

use App\Entity\AeosEntityInterface;
use App\Entity\Template;
use App\Entity\User;
use App\Service\AeosService;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeCrudActionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @see https://symfony.com/bundles/EasyAdminBundle/current/events.html#event-subscriber-example
 */
class EasyAdminSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly AeosService $aeosService
    ) {
    }

    public function setAeosData(BeforeCrudActionEvent $event)
    {
        $entity = $event->getAdminContext()->getEntity()?->getInstance();
        if ($entity instanceof AeosEntityInterface) {
            $id = $entity->getAeosId();
            $data = match ($entity::class) {
                Template::class => $this->aeosService->getTemplate($id),
                User::class => $this->aeosService->getPerson($id),
                default => throw new \RuntimeException(sprintf('Invalid class %s', $entity::class))
            };
            $entity->setAeosData((array) $data);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            BeforeCrudActionEvent::class => ['setAeosData'],
        ];
    }
}
