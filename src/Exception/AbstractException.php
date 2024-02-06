<?php

/*
 * This file is part of Gæstehåndtering.
 *
 * (c) 2017–2024 ITK Development
 *
 * This source file is subject to the MIT license.
 */

namespace App\Exception;

class AbstractException extends \Exception
{
    protected $context;

    public function __construct($message = '', ?array $context = null, $code = 0, ?\Throwable $previous = null)
    {
        $this->context = $context;
        parent::__construct($message, $code, $previous);
    }
}
