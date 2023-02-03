<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2020 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Service;

use App\Entity\Code;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class AeosHelper
{
    /** @var \App\Service\AeosService */
    protected $aeosService;

    /** @var \Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface */
    protected $tokenStorage;

    /** @var TwigHelper */
    protected $twigHelper;

    /** @var Configuration */
    protected $configuration;

    public function __construct(
        AeosService $aeosService,
        TokenStorageInterface $tokenStorage,
        TwigHelper $twigHelper,
        Configuration $configuration
    ) {
        $this->aeosService = $aeosService;
        $this->tokenStorage = $tokenStorage;
        $this->twigHelper = $twigHelper;
        $this->configuration = $configuration;
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
            throw new \Exception('Template has no aeos id');
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
                $template = $this->configuration->get('aeos_vistor_name_template');
                $visitorName = $this->twigHelper
                    ->renderTemplate($template, [
                        'code' => $code,
                        'user' => $user,
                    ]);
            } catch (\Exception $e) {
            }
        }
        $visitorName = trim($visitorName);

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
                $user = $this->tokenStorage->getToken()->getUser();
            }

            return null !== $this->aeosService->getPerson($user->getAeosId());
        } catch (\Exception $ex) {
        }

        return false;
    }
}
