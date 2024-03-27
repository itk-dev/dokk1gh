<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Validator\Constraints;

use App\Service\AeosService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AeosPersonIdValidator extends ConstraintValidator
{
    public function __construct(
        private readonly AeosService $aeosService
    ) {
    }

    public function validate(mixed $value, Constraint $constraint)
    {
        \assert($constraint instanceof AeosPersonId);
        $person = $value ? $this->aeosService->getPerson($value) : null;
        if (!$person) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value ?? '')
                ->addViolation();
        }
    }
}
