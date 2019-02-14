<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2019 ITK Development
 *
 * This source file is subject to the MIT license.
 */

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
        $person = $value ? $this->aeosService->getPerson($value) : null;
        if (!$person) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
