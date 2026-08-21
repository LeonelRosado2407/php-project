<?php

declare(strict_types=1);

namespace Keel\Config;

use Keel\Exception\MissingConfigurationException;

final class Settings
{
    public function __construct(private readonly array $items)
    {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->items;

        foreach (explode('.', $key) as $segment) {
            if (!is_array($value) || !array_key_exists($segment, $value)) {
                return $default;
            }

            $value = $value[$segment];
        }

        return $value;
    }

    /**
     * Igual que get(), pero lanza MissingConfigurationException si el valor
     * resuelto es null — para config sin la cual la app no puede arrancar
     * de forma segura (credenciales de BD, secreto de JWT, etc.).
     */
    public function require(string $key): mixed
    {
        $value = $this->get($key);

        if ($value === null) {
            throw new MissingConfigurationException($key);
        }

        return $value;
    }
}
