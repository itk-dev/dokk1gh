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

class AeosTemplateIdValidator extends ConstraintValidator
{
    public function __construct(
        private readonly AeosService $aeosService
    ) {
    }

    public function validate($value, Constraint $constraint)
    {
        $template = $value ? $this->aeosService->getTemplate($value) : null;
        if (!$template) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
