<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Keel\Database\Seed\DatabaseSeeder;
use Keel\Database\Seed\UserSeeder;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/../src/Config/container.php');
$container = $containerBuilder->build();

$pdo = $container->get(PDO::class);

$class = $argv[1] ?? null;

if ($class) {
    // correr un seeder específico, ej: php bin/seed.php UserSeeder
    $fqcn = "Keel\\Database\\Seed\\{$class}";

    if (!class_exists($fqcn)) {
        echo "No existe el seeder: {$class}\n";
        exit(1);
    }

    (new $fqcn($pdo))->run();
} else {
    // correr todos, vía DatabaseSeeder
    (new DatabaseSeeder($pdo))->run();
}