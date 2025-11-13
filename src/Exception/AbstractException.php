<?php

namespace App\Exception;

class AbstractException extends \Exception
{
    protected ?array $context;

    public function __construct(string $message = '', ?array $context = null, int $code = 0, ?\Throwable $previous = null)
    {
        $this->context = $context;
        parent::__construct($message, $code, $previous);
    }
}
