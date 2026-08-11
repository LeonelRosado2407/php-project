<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

// 1. Cargar variables de entorno
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// 2. Contenedor
$containerBuilder = new ContainerBuilder();
$containerBuilder->addDefinitions(__DIR__ . '/../src/Config/container.php');
$container = $containerBuilder->build();

// 3. App
AppFactory::setContainer($container);
$app = AppFactory::create();

// 4. Middlewares
$app->add(\App\Middleware\SessionMiddleware::class);
(require __DIR__ . '/../src/Config/middleware.php')($app);

// 5. Rutas
(require __DIR__ . '/../src/Routes/web.php')($app);

$app->run();