<?php

namespace App\Controller;

use App\DTO\LoginRequestDTO;
use App\Service\AuthService;
use App\Service\JwtService;
use App\Validation\LoginValidator;
use App\Exception\InvalidCredentialsException;
use App\Exception\UserNotFoundException;
use App\Exception\ValidationException;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController
{
    public function __construct(
        private AuthService $authService,
        private JwtService $jwtService,
        private LoginValidator $loginValidator,
        private LoggerInterface $logger,
    ) {}

    public function login(Request $request, Response $response): Response
    {
        $dto = LoginRequestDTO::fromArray((array) $request->getParsedBody());

        try {
            $this->loginValidator->validate($dto);
        } catch (ValidationException $e) {
            $response->getBody()->write(json_encode(['errors' => $e->getErrors()]));
            return $response->withStatus(422)->withHeader('Content-Type', 'application/json');
        }

        try {
            $user = $this->authService->attempt($dto->email, $dto->password);
        } catch (UserNotFoundException|InvalidCredentialsException $e) {
            $this->logger->warning('Intento de login fallido', ['email' => $dto->email, 'reason' => $e->getMessage()]);

            $response->getBody()->write(json_encode(['error' => 'Credenciales inválidas']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        $this->logger->info('Login exitoso', ['user_id' => $user->id, 'email' => $dto->email]);

        $token = $this->jwtService->generate($user->id);

        $response->getBody()->write(json_encode(['message' => 'Login exitoso', 'token' => $token]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}