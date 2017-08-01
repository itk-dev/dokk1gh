<?php

namespace AppBundle\Validator\Constraints;

use AppBundle\Service\AeosService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AeosTemplateIdValidator extends ConstraintValidator {
    /** @var \AppBundle\Service\AeosService */
    private $aeosService;

    public function __construct(AeosService $aeosService) {
        $this->aeosService = $aeosService;
    }

    public function validate($value, Constraint $constraint) {
        $template = $this->aeosService->getTemplate($value);
        if (!$template) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
