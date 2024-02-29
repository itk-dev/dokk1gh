<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\EventSubscriber;

use App\Service\GdprHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class GdprSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly TokenStorageInterface $tokenStorage,
        private readonly GdprHelper $helper
    ) {
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => 'checkGdprRequest',
        ];
    }

    public function checkGdprRequest(RequestEvent $event): void
    {
        if (HttpKernel::MAIN_REQUEST !== $event->getRequestType()) {
            // don't do anything if it's not the master request
            return;
        }

        $token = $this->tokenStorage->getToken();
        if (null === $token) {
            return;
        }

        $request = $event->getRequest();
        // Skip check on GDPR routes (user must be able to perform a request that saves accept of GDPR).
        if (\in_array($request->get('_route'), ['gdpr_show', 'gdpr_accept'], true)) {
            return;
        }

        $user = $token->getUser();
        if ($user instanceof UserInterface && !$this->helper->isGdprAccepted($user)) {
            $redirectUrl = $this->helper->getRedirectUrl();

            $currentPath = $request->getPathInfo();
            $redirectInfo = parse_url($redirectUrl);

            // Only redirect if not already on redirect target path.
            $doRedirect = $redirectInfo['path'] !== $currentPath;

            if ($doRedirect) {
                // Add current url to redirect url.
                $referrer = $request->getPathInfo();
                if (null !== $request->getQueryString()) {
                    $referrer .= '?'.$request->getQueryString();
                }
                $redirectUrl .= (!str_contains($redirectUrl, '?') ? '?' : '&')
                  .'referrer='.urlencode($referrer);
                $event->setResponse(new RedirectResponse($redirectUrl));
            }
        }
    }
}
