<?php

namespace AppBundle\Validator\Constraints;

use AppBundle\Service\AeosService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AeosPersonIdValidator extends ConstraintValidator
{
    /** @var \AppBundle\Service\AeosService */
    private $aeosService;

    public function __construct(AeosService $aeosService)
    {
        $this->aeosService = $aeosService;
    }

    public function validate($value, Constraint $constraint)
    {
        $person = $this->aeosService->getPerson($value);
        if (!$person) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
