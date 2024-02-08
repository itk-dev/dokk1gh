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
use App\Entity\Code;
use App\Entity\Template;
use App\Entity\User;
use App\Service\AeosHelper;
use App\Service\AeosService;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeCrudActionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityDeletedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\BeforeEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\Translation\TranslatableMessage;
use Twig\Environment;

/**
 * @see https://symfony.com/bundles/EasyAdminBundle/current/events.html#event-subscriber-example
 */
class EasyAdminSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly AeosService $aeosService,
        private readonly AeosHelper $aeosHelper,
        private readonly Environment $twig,
        private readonly RequestStack $requestStack
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

    public function setCodeIdentifier(BeforeEntityPersistedEvent|BeforeEntityUpdatedEvent $event)
    {
        $entity = $event->getEntityInstance();
        if (!($entity instanceof Code)) {
            return;
        }

        if (null === $entity->getIdentifier()) {
            $this->createAeosIdentifier($entity);
        }
    }

    public function removeCodeIdentifier(BeforeEntityDeletedEvent $event)
    {
        $entity = $event->getEntityInstance();
        if (!($entity instanceof Code)) {
            return;
        }

        if (null !== $entity->getIdentifier()) {
            $this->removeAeosIdentifier($entity);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            BeforeCrudActionEvent::class => ['setAeosData'],
            BeforeEntityPersistedEvent::class => ['setCodeIdentifier'],
            BeforeEntityUpdatedEvent::class => ['setCodeIdentifier'],
            BeforeEntityDeletedEvent::class => ['removeCodeIdentifier'],
        ];
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
        $session = $this->requestStack->getSession();
        if ($session instanceof FlashBagAwareSessionInterface) {
            // If message looks like a twig template filename we render it as a template.
            if (preg_match('/\.(html|txt)\.twig$/', $message)) {
                $message = $this->twig->render($message, $parameters);
                $parameters = [];
            }

            $session->getFlashBag()->add($type, new TranslatableMessage($message, $parameters));
        }
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
