<?php

namespace App\Exception;

class AbstractException extends \Exception
{
    public function __construct(string $message = '', protected ?array $context = null, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
