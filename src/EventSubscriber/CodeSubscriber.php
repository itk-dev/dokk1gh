<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2020 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\EventSubscriber;

use App\Entity\Code;
use App\Service\AeosHelper;
use EasyCorp\Bundle\EasyAdminBundle\Event\EasyAdminEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class CodeSubscriber implements EventSubscriberInterface
{
    /** @var AeosHelper */
    private $aeosHelper;

    /** @var FlashBagInterface */
    private $flashBag;

    /** @var Environment */
    private $twig;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(AeosHelper $aeosHelper, FlashBagInterface $flashBag, Environment $twig, TranslatorInterface $translator)
    {
        $this->aeosHelper = $aeosHelper;
        $this->flashBag = $flashBag;
        $this->twig = $twig;
        $this->translator = $translator;
    }

    public static function getSubscribedEvents()
    {
        return [
            EasyAdminEvents::PRE_PERSIST => ['setIdentifier'],
            EasyAdminEvents::PRE_UPDATE => ['setIdentifier'],
            EasyAdminEvents::PRE_REMOVE => ['removeIdentifier'],
        ];
    }

    public function setIdentifier(GenericEvent $event)
    {
        $entity = $event->getSubject();

        if (!($entity instanceof Code)) {
            return;
        }

        if (null === $entity->getIdentifier()) {
            $this->createAeosIdentifier($entity);
        }
    }

    public function removeIdentifier(GenericEvent $event)
    {
        $entity = $event->getSubject();

        if (!($entity instanceof Code)) {
            return;
        }

        if (null !== $entity->getIdentifier()) {
            $this->removeAeosIdentifier($entity);
        }
    }

    protected function showSuccess(string $message, array $parameters = [])
    {
        $this->showMessage('success', $message, $parameters);
    }

    protected function showInfo(string $message, array $parameters = [])
    {
        $this->showMessage('info', $message, $parameters);
    }

    protected function showWarning(string $message, array $parameters = [])
    {
        $this->showMessage('warning', $message, $parameters);
    }

    protected function showError(string $message, array $parameters = [])
    {
        $this->showMessage('error', $message, $parameters);
    }

    protected function showMessage(string $type, string $message, array $parameters = [])
    {
        // If message looks like a twig template filename we render it as a template.
        if (preg_match('/\.(html|txt)\.twig$/', $message)) {
            $message = $this->twig->render($message, $parameters);
            $parameters = [];
        }

        $this->flashBag->add($type, $this->translator->trans($message, $parameters));
    }

    private function createAeosIdentifier(Code $code)
    {
        try {
            $this->aeosHelper->createAeosIdentifier($code);
            $this->showSuccess('code_created.html.twig', ['code' => $code]);
        } catch (\Exception $ex) {
            $this->showError($ex->getMessage());
        }
    }

    private function removeAeosIdentifier(Code $code)
    {
        try {
            $this->aeosHelper->deleteAeosIdentifier($code);
            $this->showInfo('Code removed');
        } catch (\Exception $ex) {
            $this->showError($ex->getMessage());
        }
    }
}
