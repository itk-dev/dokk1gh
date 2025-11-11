<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class AeosPersonId extends Constraint
{
    public string $message = '"{{ string }}" is not a valid AEOS person id.';
}
