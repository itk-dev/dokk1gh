<?php

namespace AppBundle\EventSubscriber;

use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PasswordResettingSubscriber implements EventSubscriberInterface {
    private $router;

    public function __construct(UrlGeneratorInterface $router) {
        $this->router = $router;
    }

    public static function getSubscribedEvents() {
        return [
          FOSUserEvents::CHANGE_PASSWORD_SUCCESS => 'onPasswordChangeSuccess',
        ];
    }

    public function onPasswordChangeSuccess(FormEvent $event) {
      $url = $this->router->generate('easyadmin');
      $event->setResponse(new RedirectResponse($url));
    }
}
