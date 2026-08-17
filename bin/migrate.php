<?php
// bin/migrate.php

declare(strict_types=1);

use DI\ContainerBuilder;
use App\Database\Schema\SchemaBuilder;
use App\Database\Migrator;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/../src/Config/container.php');
$container = $containerBuilder->build();

$pdo = $container->get(PDO::class);
$schema = new SchemaBuilder($pdo);
$migrator = new Migrator($pdo, $schema, __DIR__ . '/../src/Database/Migration');

// $argv[0] = "bin/migrate.php", $argv[1] = comando, $argv[2] = argumento opcional
$command = $argv[1] ?? 'up';
$argument = $argv[2] ?? null;

match ($command) {
    'up' => $migrator->up(),
    'rollback' => $migrator->rollback($argument ? (int) $argument : 1),
    'rollback:file' => $argument
        ? $migrator->rollbackSpecific($argument)
        : print("Debes indicar el nombre del archivo.\n"),
    'run' => $argument
        ? $migrator->runSpecific($argument)
        : print("Debes indicar el nombre del archivo.\n"),
    'status' => $migrator->status(),
    default => print("Comando no reconocido: {$command}\n"),
};