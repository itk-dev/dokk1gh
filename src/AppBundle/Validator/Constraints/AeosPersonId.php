<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class AeosPersonId extends Constraint
{
    public $message = '"{{ string }}" is not a valid AEOS person id.';
}
