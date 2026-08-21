<?php
// src/Config/middleware.php

declare(strict_types=1);

use Slim\App;
use Keel\Config\Settings;
use Keel\Middleware\ErrorHandler;

return function (App $app) {
    $app->addRoutingMiddleware();
    $app->addBodyParsingMiddleware();

    $container = $app->getContainer();
    $displayErrorDetails = $container->get(Settings::class)->get('app.debug');

    $errorMiddleware = $app->addErrorMiddleware(
        $displayErrorDetails,
        true,  // logErrors (loguea al log de PHP por defecto, aparte del tuyo)
        true   // logErrorDetails
    );

    $errorMiddleware->setDefaultErrorHandler($container->get(ErrorHandler::class));
};