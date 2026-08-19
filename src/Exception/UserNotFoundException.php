<?php

declare(strict_types=1);

namespace Keel\Exception;

class UserNotFoundException extends \Exception
{
    public function __construct(string $message = 'Usuario no encontrado')
    {
        parent::__construct($message);
    }
}