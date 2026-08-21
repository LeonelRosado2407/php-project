<?php
// src/Database/Migrator.php

declare(strict_types=1);

namespace Keel\Database;

use PDO;
use Keel\Database\Schema\SchemaBuilder;

class Migrator
{
    public function __construct(
        private PDO $pdo,
        private SchemaBuilder $schema,
        private string $migrationsPath,
    ) {
        $this->ensureMigrationsTableExists();
    }

    private function ensureMigrationsTableExists(): void
    {
        $this->pdo->exec(
            "CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                batch INT NOT NULL,
                executed_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )"
        );
    }

    /** Corre todas las migraciones pendientes */
    public function up(): void
    {
        $alreadyRun = $this->getAlreadyRun();
        $pending = $this->getPendingFiles($alreadyRun);

        if (empty($pending)) {
            echo "Nada que migrar.\n";
            return;
        }

        $batch = $this->getNextBatch();

        foreach ($pending as $file) {
            $this->runFile($file, $batch);
        }
    }

    /** Corre un archivo específico, sin importar si hay otros pendientes antes */
    public function runSpecific(string $name): void
    {
        $file = $this->migrationsPath . '/' . $name . '.php';

        if (!file_exists($file)) {
            echo "No existe la migración: {$name}\n";
            return;
        }

        if (in_array($name, $this->getAlreadyRun())) {
            echo "Ya se había ejecutado: {$name}\n";
            return;
        }

        $batch = $this->getNextBatch();
        $this->runFile($file, $batch);
    }

    /** Revierte el último batch completo (o N batches) */
    public function rollback(int $steps = 1): void
    {
        for ($i = 0; $i < $steps; $i++) {
            $lastBatch = $this->getLastBatch();

            if ($lastBatch === null) {
                echo "No hay más migraciones que revertir.\n";
                return;
            }

            $migrations = $this->getMigrationsInBatch($lastBatch);

            // se revierten en orden inverso al que se aplicaron
            foreach (array_reverse($migrations) as $name) {
                $this->rollbackFile($name);
            }
        }
    }

    /** Revierte una migración puntual, sin importar su batch */
    public function rollbackSpecific(string $name): void
    {
        if (!in_array($name, $this->getAlreadyRun())) {
            echo "Esa migración no está aplicada: {$name}\n";
            return;
        }

        $this->rollbackFile($name);
    }

    /** Muestra el estado: qué está aplicado y qué falta */
    public function status(): void
    {
        $alreadyRun = $this->getAlreadyRun();
        $files = $this->getAllFiles();

        foreach ($files as $file) {
            $name = basename($file, '.php');
            $state = in_array($name, $alreadyRun) ? '[X] aplicada' : '[ ] pendiente';
            echo "{$state}  {$name}\n";
        }
    }

    // ── privados ─────────────────────────────

    private function runFile(string $file, int $batch): void
    {
        $name = basename($file, '.php');
        $migration = require $file;

        $migration->up($this->schema);

        $stmt = $this->pdo->prepare(
            'INSERT INTO migrations (migration, batch) VALUES (?, ?)'
        );
        $stmt->execute([$name, $batch]);

        echo "Migrada:  {$name}\n";
    }

    private function rollbackFile(string $name): void
    {
        $file = $this->migrationsPath . '/' . $name . '.php';

        if (!file_exists($file)) {
            echo "Advertencia: no se encontró el archivo de {$name}, no se puede revertir su schema.\n";
        } else {
            $migration = require $file;
            $migration->down($this->schema);
        }

        $stmt = $this->pdo->prepare('DELETE FROM migrations WHERE migration = ?');
        $stmt->execute([$name]);

        echo "Revertida: {$name}\n";
    }

    private function getAllFiles(): array
    {
        $files = glob($this->migrationsPath . '/*.php');
        sort($files);
        return $files;
    }

    private function getPendingFiles(array $alreadyRun): array
    {
        return array_filter(
            $this->getAllFiles(),
            fn($file) => !in_array(basename($file, '.php'), $alreadyRun)
        );
    }

    private function getAlreadyRun(): array
    {
        $stmt = $this->pdo->query('SELECT migration FROM migrations');
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function getNextBatch(): int
    {
        $stmt = $this->pdo->query('SELECT MAX(batch) FROM migrations');
        $max = $stmt->fetchColumn();
        return $max ? ((int) $max + 1) : 1;
    }

    private function getLastBatch(): ?int
    {
        $stmt = $this->pdo->query('SELECT MAX(batch) FROM migrations');
        $max = $stmt->fetchColumn();
        return $max !== null ? (int) $max : null;
    }

    private function getMigrationsInBatch(int $batch): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT migration FROM migrations WHERE batch = ? ORDER BY id ASC'
        );
        $stmt->execute([$batch]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}