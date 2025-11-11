<?php

namespace App\Validator\Constraints;

use App\Service\AeosService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class AeosTemplateIdValidator extends ConstraintValidator
{
    public function __construct(
        private readonly AeosService $aeosService,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint)
    {
        \assert($constraint instanceof AeosTemplateId);
        $template = $value ? $this->aeosService->getTemplate($value) : null;
        if (!$template) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value ?? '')
                ->addViolation();
        }
    }
}
