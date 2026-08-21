<?php

declare(strict_types=1);

namespace Keel\Exception;

class EmailAlreadyExistsException extends \Exception
{
    public function __construct(string $message = 'El email ya está registrado')
    {
        parent::__construct($message);
    }
}