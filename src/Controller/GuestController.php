<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2020 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Controller;

use App\Entity\Guest;
use App\Service\GuestService;
use App\Service\TemplateManager;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class GuestController extends AdminController
{
    /** @var GuestService */
    private $guestService;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        TemplateManager $templateManager,
        Environment $twig,
        TranslatorInterface $translator,
        GuestService $guestService
    ) {
        parent::__construct($tokenStorage, $templateManager, $twig, $translator);
        $this->guestService = $guestService;
    }

    public function createNewGuestEntity()
    {
        return $this->guestService->createNewGuest();
    }

    public function showAppAction()
    {
        $guest = $this->getGuest();

        return $this->redirectToRoute('app_code', [
            'guest' => $guest->getId(),
        ]);
    }

    public function sendAppAction()
    {
        $guest = $this->getGuest();
        if (null !== $guest) {
            $appUrl = $this->generateUrl('app_code', ['guest' => $guest->getId()], UrlGeneratorInterface::ABSOLUTE_URL);
            if ($this->guestService->sendApp($guest, $appUrl)) {
                $this->addFlash('info', 'App sent');
            }
        }

        return $this->redirectToReferrer();
    }

    public function resendAppAction()
    {
        return $this->sendAppAction();
    }

    public function expireAppAction()
    {
        $guest = $this->getGuest();
        if (null !== $guest) {
            if ($this->guestService->expire($guest)) {
                $this->addFlash('info', 'Guest '.$guest->getId().' expired');
            }
        }

        return $this->redirectToReferrer();
    }

    /**
     * @return null|Guest
     */
    private function getGuest()
    {
        $id = $this->request->query->get('id');

        return  $this->em->getRepository(Guest::class)->find($id);
    }
}
