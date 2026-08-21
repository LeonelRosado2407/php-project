<?php

declare(strict_types=1);

namespace Keel\Exception;

class ValidationException extends \Exception
{
    /** @param array<string, string> $errors */
    public function __construct(private array $errors)
    {
        parent::__construct('Error de validación');
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}