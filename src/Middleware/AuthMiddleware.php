<?php

declare(strict_types=1);

namespace Keel\Middleware;

use Keel\Service\JwtService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response as SlimResponse;

class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(private JwtService $jwtService) {}

    public function process(Request $request, Handler $handler): Response
    {
        $header = $request->getHeaderLine('Authorization'); // "Bearer xxxxx"

        if (!$header || !str_starts_with($header, 'Bearer ')) {
            return $this->unauthorized('Token no proporcionado');
        }

        $token = substr($header, 7); // quita "Bearer "
        $userId = $this->jwtService->verify($token);

        if (!$userId) {
            return $this->unauthorized('Token inválido o expirado');
        }

        // Guardamos el user_id en el Request para que el controller lo use después
        $request = $request->withAttribute('user_id', $userId);

        return $handler->handle($request);
    }

    private function unauthorized(string $message): Response
    {
        $response = new SlimResponse();
        $response->getBody()->write(json_encode(['error' => $message]));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }
}