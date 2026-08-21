<?php

declare(strict_types=1);

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        if (!array_key_exists($key, $_ENV)) {
            return $default;
        }

        $value = $_ENV[$key];

        return match (true) {
            $value === 'true' => true,
            $value === 'false' => false,
            $value === 'null' => null,
            is_numeric($value) => $value + 0,
            default => $value,
        };
    }
}
