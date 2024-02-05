<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Service;

use App\Entity\Code;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final readonly class AeosHelper
{
    public function __construct(
        private readonly AeosService $aeosService,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly TwigHelper $twigHelper,
        private readonly array $options
    ) {
    }

    public function createAeosIdentifier(Code $code, $visitorName = null)
    {
        $user = $code->getCreatedBy() ?? $this->tokenStorage->getToken()->getUser();
        if (!$user) {
            throw new \Exception('Code has no user');
        }
        $template = $code->getTemplate();
        if (!$template) {
            throw new \Exception('Code has no template');
        }
        if (!$template->getAeosId()) {
            throw new \Exception('Template has no AEOS id');
        }

        $aeosContactPerson = $this->aeosService->getPerson($user->getAeosId());
        if (!$aeosContactPerson) {
            throw new \Exception('Cannot find AEOS person: '.$user->getAeosId());
        }

        $aeosTemplate = $this->aeosService->getTemplate($template->getAeosId());
        if (!$aeosTemplate) {
            throw new \Exception('Cannot find AEOS template: '.$template->getAeosId());
        }

        if (null === $visitorName) {
            try {
                $template = $this->options['aeos_visitor_name_template'];
                $visitorName = $this->twigHelper
                    ->renderTemplate($template, [
                        'code' => $code,
                        'user' => $user,
                    ]);
            } catch (\Exception) {
            }
        }
        $visitorName = trim((string) $visitorName);

        $visitor = $this->aeosService->createVisitor([
            'UnitId' => $aeosContactPerson->UnitId,
            'LastName' => $visitorName,
        ]);

        $this->aeosService->setVerificationState($visitor, false);
        $this->aeosService->createVisit(
            $visitor,
            $aeosContactPerson,
            $code->getStartTime(),
            $code->getEndTime(),
            $aeosTemplate
        );
        $identifier = $this->aeosService->createIdentifier($visitor, $aeosContactPerson);

        $code->setIdentifier($identifier->BadgeNumber);
    }

    public function deleteAeosIdentifier(Code $code)
    {
        $identifier = $this->aeosService->getIdentifierByBadgeNumber($code->getIdentifier());
        $visitor = $identifier ? $this->aeosService->getVisitorByIdentifier($identifier) : null;
        $visit = $visitor ? $this->aeosService->getVisitByVisitor($visitor) : null;

        if ($identifier && !$this->aeosService->isBlocked($identifier)) {
            $this->aeosService->blockIdentifier($identifier);
        }
        if ($visit) {
            $this->aeosService->deleteVisit($visit);
        }
        if ($visitor) {
            $this->aeosService->deleteVisitor($visitor);
        }
    }

    public function userHasAeosId(User $user = null)
    {
        try {
            if (null === $user) {
                $user = $this->tokenStorage->getToken()?->getUser();
            }

            return null !== $user
                && null !== $this->aeosService->getPerson($user->getAeosId());
        } catch (\Exception) {
        }

        return false;
    }
}
