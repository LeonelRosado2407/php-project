<?php

namespace Keel\Controller;

use Keel\DTO\LoginRequestDTO;
use Keel\DTO\RegisterRequestDTO;
use Keel\Exception\EmailAlreadyExistsException;
use Keel\Http\JsonResponder;
use Keel\Service\AuthService;
use Keel\Service\JwtService;
use Keel\Validation\LoginValidator;
use Keel\Exception\InvalidCredentialsException;
use Keel\Exception\UserNotFoundException;
use Keel\Validation\RegisterValidator;
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthController
{
    use JsonResponder;

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

        if ($error = $this->validateOrFail($response, fn () => $this->loginValidator->validate($dto))) {
            return $error;
        }

        try {
            $user = $this->authService->attempt($dto->email, $dto->password);
        } catch (UserNotFoundException|InvalidCredentialsException $e) {
            $this->logger->warning('Intento de login fallido', ['email' => $dto->email, 'reason' => $e->getMessage()]);

            return $this->jsonError($response, 'Credenciales inválidas', 401);
        }

        $this->logger->info('Login exitoso', ['user_id' => $user->id, 'email' => $dto->email]);

        $token = $this->jwtService->generate($user->id);

        return $this->json($response, ['message' => 'Login exitoso', 'token' => $token]);
    }

    public function register(Request $request, Response $response): Response
    {
        $dto = RegisterRequestDTO::fromArray((array) $request->getParsedBody());

        if ($error = $this->validateOrFail($response, fn () => $this->registerValidator->validate($dto))) {
            return $error;
        }

        try {
            $user = $this->authService->register($dto->name, $dto->email, $dto->password);
        } catch (EmailAlreadyExistsException $e) {
            return $this->jsonError($response, $e->getMessage(), 409);
        }

        $this->logger->info('Usuario registrado', ['user_id' => $user->id, 'email' => $user->email]);

        $token = $this->jwtService->generate($user->id);

        return $this->json($response, [
            'message' => 'Usuario registrado exitosamente',
            'token' => $token,
        ], 201);
    }
}