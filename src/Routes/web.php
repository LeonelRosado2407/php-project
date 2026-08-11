<?php

declare(strict_types=1);

use Slim\App;
use App\Controller\AuthController;
use App\Middleware\AuthMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return function (App $app) {
    $app->post('/login', [AuthController::class, 'login']);

    // Ruta protegida de ejemplo
    $app->get('/dashboard', function (Request $request, Response $response) {
        $response->getBody()->write(json_encode(['message' => 'Bienvenido', 'user_id' => $_SESSION['user_id']]));
        return $response->withHeader('Content-Type', 'application/json');
    })->add(AuthMiddleware::class);
};