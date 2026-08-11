<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\AuthService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController
{
    public function __construct(private AuthService $authService) {}

    public function login(Request $request, Response $response): Response
    {
        $data = (array) $request->getParsedBody();

        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        $user = $this->authService->attempt($email, $password);

        if (!$user) {
            $response->getBody()->write(json_encode(['error' => 'Credenciales inválidas']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        // Aquí es donde el controller SÍ sabe de HTTP: crea la sesión
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user->id;

        $response->getBody()->write(json_encode(['message' => 'Login exitoso', 'user' => $user->name]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}