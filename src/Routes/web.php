<?php
// src/Routes/web.php

declare(strict_types=1);

use Keel\Controller\AuthController;
use Slim\App;
use Keel\Middleware\AuthMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return function (App $app) {
    // Rutas públicas
    $app->post('/login', [AuthController::class, 'login']);
    $app->post('/register', [AuthController::class, 'register']);

    // Rutas protegidas con JWT, agrupadas
    $app->group('', function ($group) {
        $group->get('/dashboard', function (Request $request, Response $response) {
            $userId = $request->getAttribute('user_id');
            $response->getBody()->write(json_encode(['message' => 'Bienvenido', 'user_id' => $userId]));
            return $response->withHeader('Content-Type', 'application/json');
        });

        $group->get('/profile', function (Request $request, Response $response) {
            $userId = $request->getAttribute('user_id');
            return $response;
        });
    })->add(AuthMiddleware::class);
};