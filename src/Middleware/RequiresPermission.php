<?php

declare(strict_types=1);

namespace Keel\Middleware;

use Keel\Http\JsonResponder;
use Keel\Service\AuthorizationService;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response as SlimResponse;

class RequiresPermission implements MiddlewareInterface
{
    use JsonResponder;

    public function __construct(
        private ContainerInterface $container,
        private string $permission,
    ) {}

    public function process(Request $request, Handler $handler): Response
    {
        $userId = $request->getAttribute('user_id');

        // Resuelto perezosamente: instanciar este middleware pasa por
        // web.php en CADA request (no hay cacheo de rutas), así que si
        // resolviéramos AuthorizationService en el constructor, abriríamos
        // una conexión PDO real en cada petición, incluso las que no
        // pasan por esta ruta.
        $authorizationService = $this->container->get(AuthorizationService::class);

        if (!$userId || !$authorizationService->can((int) $userId, $this->permission)) {
            return $this->jsonError(new SlimResponse(), 'No autorizado', 403);
        }

        return $handler->handle($request);
    }
}
