<?php

declare(strict_types=1);

use App\Factory\LoggerFactory;
use App\Middleware\ErrorHandler;
use App\Repository\Contract\UserRepositoryInterface;
use App\Repository\UserRepository;
use App\Service\JwtService;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

return [
    PDO::class => function (ContainerInterface $c) {
        $host = $_ENV['DB_HOST'];
        $port = $_ENV['DB_PORT'];
        $database = $_ENV['DB_DATABASE'];
        $username = $_ENV['DB_USERNAME'];
        $password = $_ENV['DB_PASSWORD'];

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

        return new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    },

    ErrorHandler::class => function (ContainerInterface $c) {
        return new ErrorHandler(
            logger: $c->get(LoggerInterface::class),
            displayErrorDetails: $_ENV['APP_ENV'] === 'development',
        );
    },
    
    JwtService::class => function (ContainerInterface $c) {
        return new JwtService(
            secret: $_ENV['JWT_SECRET'],
            expiration: (int) $_ENV['JWT_EXPIRATION']
        );
    },

    LoggerInterface::class => function (ContainerInterface $c) {
        return LoggerFactory::create('app');
    },
    
    UserRepositoryInterface::class => \DI\autowire(UserRepository::class),
];