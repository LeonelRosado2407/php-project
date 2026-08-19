<?php

declare(strict_types=1);

/**
 * Separa $argv en:
 * - 'positional': argumentos que no empiezan con --
 * - 'flags': asociativo, ej. ['table' => 'posts', 'force' => true]
 */
function parseArgs(array $argv): array
{
    $positional = [];
    $flags = [];

    // Empezamos en 1 para saltar argv[0] (el nombre del script)
    for ($i = 1; $i < count($argv); $i++) {
        $arg = $argv[$i];

        if (str_starts_with($arg, '--')) {
            $withoutDashes = substr($arg, 2); // quita "--"

            if (str_contains($withoutDashes, '=')) {
                // Caso: --table=posts
                [$key, $value] = explode('=', $withoutDashes, 2);
                $flags[$key] = $value;
            } else {
                // Caso: --force (switch booleano)
                $flags[$withoutDashes] = true;
            }
        } else {
            $positional[] = $arg;
        }
    }

    return [$positional, $flags];
}