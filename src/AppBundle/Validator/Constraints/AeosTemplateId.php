<?php

namespace AppBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class AeosTemplateId extends Constraint
{
    public $message = '"{{ string }}" is not a valid AEOS template id.';
}
