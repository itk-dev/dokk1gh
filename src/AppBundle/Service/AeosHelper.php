<?php

namespace AppBundle\Service;

use AppBundle\Entity\Code;

class AeosHelper
{
    protected $aeosService;

    public function __construct(AeosService $aeosService)
    {
        $this->aeosService = $aeosService;
    }

    public function createAeosIdentifier(Code $code)
    {
        $user = $code->getCreatedBy();
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
            throw new \Exception('Cannot find AEOS person: ' . $user->getAeosId());
        }

        $aeosTemplate = $this->aeosService->getTemplate($template->getAeosId());
        if (!$aeosTemplate) {
            throw new \Exception('Cannot find AEOS template: ' . $template->getAeosId());
        }

        $visitorName = 'dokk1gh: code: #' . $code->getId() . '; ' . (new \DateTime())->format(\DateTime::ISO8601);

        $visitor = $this->aeosService->createVisitor([
            'UnitId' => $aeosContactPerson->UnitId,
            'LastName' => $visitorName,
        ]);

        $states = $this->aeosService->setVerificationState($visitor, false);
        $visit = $this->aeosService->createVisit($visitor, $aeosContactPerson, $code->getStartTime(), $code->getEndTime(), $aeosTemplate);
        $identifier = $this->aeosService->createIdentifier($visitor, $aeosContactPerson);

        $code->setIdentifier($identifier->BadgeNumber);
    }

    public function deleteAeosIdentifier(Code $code) {
        $identifier = $this->aeosService->getIdentifierByBadgeNumber($code->getIdentifier());
        $visitor = $identifier ? $this->aeosService->getVisitorByIdentifier($identifier) : null;
        $visit = $visitor ? $this->aeosService->getVisitByVisitor($visitor) : null;

        if ($identifier && !$this->aeosService->isDeleted($identifier)) {
            $result = $this->aeosService->deleteIdentifier($identifier);
        }
        if ($visit) {
            $result = $this->aeosService->deleteVisit($visit);
        }
        if ($visitor) {
            $result = $this->aeosService->deleteVisitor($visitor);
        }
    }
}
