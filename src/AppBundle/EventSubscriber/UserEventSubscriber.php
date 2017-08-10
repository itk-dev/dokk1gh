<?php

namespace AppBundle\EventSubscriber;

use AppBundle\Entity\User;
use AppBundle\Service\AeosService;
use JavierEguiluz\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

class UserEventSubscriber implements EventSubscriberInterface
{
    /** @var \AppBundle\Service\AeosService */
    private $aeosService;

    public function __construct(AeosService $aeosService)
    {
        $this->aeosService = $aeosService;
    }

    public static function getSubscribedEvents()
    {
        return [
            EasyAdminEvents::PRE_EDIT => ['loadAeosPerson'],
            EasyAdminEvents::PRE_SHOW => ['loadAeosPerson'],
        ];
    }

    public function loadAeosPerson(GenericEvent $event)
    {
        $entity = $event->getArgument('request')->attributes->get('easyadmin')['item'];

        if ($entity instanceof User) {
            $person = $this->aeosService->getPerson($entity->getAeosId());
            $entity->setAeosPerson($person);
        }
    }
}
