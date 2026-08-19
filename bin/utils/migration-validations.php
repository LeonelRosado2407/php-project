<?php

declare(strict_types=1);

/**
 * Busca si ya existe una migración cuyo nombre descriptivo
 * (ignorando el timestamp) coincida con $name.
 */
function findExistingMigration(string $migrationsDir, string $name): ?string
{
    $files = glob($migrationsDir . '/*.php');

    foreach ($files as $file) {
        $fileName = basename($file, '.php');

        // Quita el timestamp del inicio: YYYY_MM_DD_HHMMSS_
        $withoutTimestamp = preg_replace('/^\d{4}_\d{2}_\d{2}_\d{6}_/', '', $fileName);

        if ($withoutTimestamp === $name) {
            return $file; // ya existe, regresamos la ruta encontrada
        }
    }

    return null;
}