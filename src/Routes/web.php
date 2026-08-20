<?php
// src/Routes/web.php

declare(strict_types=1);

use Keel\Controller\AuthController;
use Keel\Controller\PostController;
use Keel\Middleware\RequiresPermission;
use Slim\App;
use Keel\Middleware\AuthMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return function (App $app) {
    $container = $app->getContainer();

    // Rutas públicas
    $app->post('/login', [AuthController::class, 'login']);
    $app->post('/register', [AuthController::class, 'register']);

    // Rutas protegidas con JWT, agrupadas
    $app->group('', function ($group) use ($container) {
        $group->get('/dashboard', function (Request $request, Response $response) {
            $userId = $request->getAttribute('user_id');
            $response->getBody()->write(json_encode(['message' => 'Bienvenido', 'user_id' => $userId]));
            return $response->withHeader('Content-Type', 'application/json');
        });

        $group->get('/profile', function (Request $request, Response $response) {
            $userId = $request->getAttribute('user_id');
            return $response;
        });

        $group->delete('/posts/{id:[0-9]+}', [PostController::class, 'destroy'])
            ->add(new RequiresPermission($container, 'posts.delete'));
    })->add(AuthMiddleware::class);
};
