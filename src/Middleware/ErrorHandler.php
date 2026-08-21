<?php

declare(strict_types=1);

namespace Keel\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;
use Slim\Psr7\Response as SlimResponse;
use Throwable;

class ErrorHandler
{
    public function __construct(
        private LoggerInterface $logger,
        private bool $displayErrorDetails,
    ) {}

    public function __invoke(
        Request $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): Response {
        $this->logger->error($exception->getMessage(), [
            'exception' => get_class($exception),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
        ]);

        $payload = ['error' => 'Ocurrió un error interno'];

        if ($this->displayErrorDetails) {
            $payload = [
                'error' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => explode("\n", $exception->getTraceAsString()),
            ];
        }

        $response = new SlimResponse();
        $response->getBody()->write(json_encode($payload));

        return $response
            ->withStatus(500)
            ->withHeader('Content-Type', 'application/json');
    }
}