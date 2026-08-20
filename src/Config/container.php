<?php

declare(strict_types=1);

use Keel\Config\Settings;
use Keel\Factory\LoggerFactory;
use Keel\Middleware\ErrorHandler;
use Keel\Repository\Contract\PermissionRepositoryInterface;
use Keel\Repository\Contract\PostRepositoryInterface;
use Keel\Repository\Contract\UserRepositoryInterface;
use Keel\Repository\PermissionRepository;
use Keel\Repository\PostRepository;
use Keel\Repository\UserRepository;
use Keel\Service\JwtService;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return [
    Settings::class => function () {
        return new Settings(require __DIR__ . '/config.php');
    },

    PDO::class => function (ContainerInterface $c) {
        $settings = $c->get(Settings::class);

        $host = $settings->require('db.host');
        $port = $settings->get('db.port', 3306);
        $database = $settings->require('db.database');
        $username = $settings->require('db.username');
        $password = $settings->get('db.password', '');

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

        return new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    },

    ErrorHandler::class => function (ContainerInterface $c) {
        return new ErrorHandler(
            logger: $c->get(LoggerInterface::class),
            displayErrorDetails: $c->get(Settings::class)->get('app.debug'),
        );
    },

    JwtService::class => function (ContainerInterface $c) {
        $settings = $c->get(Settings::class);

        return new JwtService(
            secret: $settings->require('jwt.secret'),
            expiration: (int) $settings->get('jwt.expiration')
        );
    },

    LoggerInterface::class => function (ContainerInterface $c) {
        return LoggerFactory::create('app');
    },

    UserRepositoryInterface::class => \DI\autowire(UserRepository::class),
    PermissionRepositoryInterface::class => \DI\autowire(PermissionRepository::class),
    PostRepositoryInterface::class => \DI\autowire(PostRepository::class),
];