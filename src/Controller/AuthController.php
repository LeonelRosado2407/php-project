<?php

namespace Keel\Controller;

use Keel\DTO\LoginRequestDTO;
use Keel\DTO\RegisterRequestDTO;
use Keel\Exception\EmailAlreadyExistsException;
use Keel\Service\AuthService;
use Keel\Service\JwtService;
use Keel\Validation\LoginValidator;
use Keel\Exception\InvalidCredentialsException;
use Keel\Exception\UserNotFoundException;
use Keel\Exception\ValidationException;
use Keel\Validation\RegisterValidator;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController
{
    public function __construct(
        private AuthService $authService,
        private JwtService $jwtService,
        private LoginValidator $loginValidator,
        private RegisterValidator $registerValidator,
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

    public function register(Request $request, Response $response): Response
    {
        $dto = RegisterRequestDTO::fromArray((array) $request->getParsedBody());

        try {
            $this->registerValidator->validate($dto);
        } catch (ValidationException $e) {
            $response->getBody()->write(json_encode(['errors' => $e->getErrors()]));
            return $response->withStatus(422)->withHeader('Content-Type', 'application/json');
        }

        try {
            $user = $this->authService->register($dto->name, $dto->email, $dto->password);
        } catch (EmailAlreadyExistsException $e) {
            $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
            return $response->withStatus(409)->withHeader('Content-Type', 'application/json');
        }

        $this->logger->info('Usuario registrado', ['user_id' => $user->id, 'email' => $user->email]);

        $token = $this->jwtService->generate($user->id);

        $response->getBody()->write(json_encode([
            'message' => 'Usuario registrado exitosamente',
            'token' => $token,
        ]));
        return $response->withStatus(201)->withHeader('Content-Type', 'application/json');
    }
}