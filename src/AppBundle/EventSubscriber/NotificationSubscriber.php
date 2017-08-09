<?php

namespace AppBundle\EventSubscriber;

use JavierEguiluz\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Translation\TranslatorInterface;

class NotificationSubscriber implements EventSubscriberInterface
{
    /** @var \Symfony\Component\HttpFoundation\Session\Session */
    private $session;

    /** @var \Symfony\Component\Translation\TranslatorInterface */
    private $translator;

    public function __construct(Session $session, TranslatorInterface $translator)
    {
        $this->session = $session;
        $this->translator = $translator;
    }

    public static function getSubscribedEvents()
    {
        return [
            // EasyAdminEvents::POST_PERSIST => ['postPersist'],
            EasyAdminEvents::POST_UPDATE => ['postUpdate'],
            EasyAdminEvents::POST_REMOVE => ['postRemove'],
        ];
    }

    public function postPersist(GenericEvent $event)
    {
        $this->showInfo(EasyAdminEvents::POST_PERSIST, $event);
    }

    public function postUpdate(GenericEvent $event)
    {
        $this->showInfo(EasyAdminEvents::POST_UPDATE, $event);
    }

    public function postRemove(GenericEvent $event)
    {
        $this->showInfo(EasyAdminEvents::POST_REMOVE, $event);
    }

    private function showInfo($eventType, GenericEvent $event)
    {
        $subject = $event->getSubject();
        if ($subject) {
            $messages = [
                EasyAdminEvents::POST_PERSIST => '%entity_type% %entity_name% created',
                EasyAdminEvents::POST_UPDATE => '%entity_type% %entity_name% updated',
                EasyAdminEvents::POST_REMOVE => '%entity_type% %entity_name% removed',
            ];
            if ($messages[$eventType]) {
                $type = get_class($subject);
                $name = $type;
                try {
                    $name = (string)$subject;
                } catch (\Exception $ex) {
                }
                $this->session->getFlashBag()->add('info', $this->translator->trans($messages[$eventType], [
                    '%entity_type%' => $this->translator->trans($type),
                    '%entity_name%' => $name,
                ]));
            }
        }
    }
}
