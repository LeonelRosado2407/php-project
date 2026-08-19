<?php

declare(strict_types=1);

namespace Keel\Exception;

class InvalidCredentialsException extends \Exception
{
    public function __construct(string $message = 'Credenciales inválidas')
    {
        parent::__construct($message);
    }
}