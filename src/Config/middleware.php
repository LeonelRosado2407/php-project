<?php
// src/Config/middleware.php

declare(strict_types=1);

use Slim\App;
use App\Middleware\ErrorHandler;

return function (App $app) {
    $app->addRoutingMiddleware();
    $app->addBodyParsingMiddleware();

    $container = $app->getContainer();
    $displayErrorDetails = $_ENV['APP_ENV'] === 'development';

    $errorMiddleware = $app->addErrorMiddleware(
        $displayErrorDetails,
        true,  // logErrors (loguea al log de PHP por defecto, aparte del tuyo)
        true   // logErrorDetails
    );

    $errorMiddleware->setDefaultErrorHandler($container->get(ErrorHandler::class));
};