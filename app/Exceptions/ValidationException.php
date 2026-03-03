<?php

namespace App\Exceptions;

use Exception;

/**
 * Application-specific validation failure (separate from Laravel's built-in one).
 */
class ValidationException extends Exception
{
    // Additional payload (errors) can be stored if needed
    protected array $errors = [];

    public function __construct(string $message = "Validation failed", array $errors = [], int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}
