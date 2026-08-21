<?php

declare(strict_types=1);

namespace Keel\Exception;

class MissingConfigurationException extends \Exception
{
    public function __construct(string $key)
    {
        parent::__construct("Falta la configuración requerida: \"{$key}\". Revisa tu archivo .env.");
    }
}
