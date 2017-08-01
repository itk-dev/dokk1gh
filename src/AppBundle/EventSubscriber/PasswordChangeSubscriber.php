<?php

namespace AppBundle\EventSubscriber;

use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PasswordChangeSubscriber implements EventSubscriberInterface
{
    private $router;

    public function __construct(UrlGeneratorInterface $router)
    {
        $this->router = $router;
    }

    public static function getSubscribedEvents()
    {
        return [
            FOSUserEvents::CHANGE_PASSWORD_SUCCESS => 'onPasswordChangeSuccess',
            FOSUserEvents::RESETTING_RESET_SUCCESS => 'onPasswordResetSuccess',
        ];
    }

    public function onPasswordChangeSuccess(FormEvent $event)
    {
        $url = $this->router->generate('easyadmin');
        $event->setResponse(new RedirectResponse($url));
    }

    public function onPasswordResetSuccess(FormEvent $event)
    {
        $url = $this->router->generate('fos_user_security_login');
        $event->setResponse(new RedirectResponse($url));
    }
}
