<?php

declare(strict_types=1);

namespace Keel\Http;

use Keel\Exception\ValidationException;
use Psr\Http\Message\ResponseInterface as Response;

trait JsonResponder
{
    protected function json(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));

        return $response->withStatus($status)->withHeader('Content-Type', 'application/json');
    }

    protected function jsonError(Response $response, string $message, int $status): Response
    {
        return $this->json($response, ['error' => $message], $status);
    }

    /** @param array<string, string> $errors */
    protected function jsonErrors(Response $response, array $errors, int $status = 422): Response
    {
        return $this->json($response, ['errors' => $errors], $status);
    }

    /**
     * Corre $validate() y, si lanza ValidationException, la convierte en una
     * respuesta 422. Devuelve null si la validación pasó (nada que retornar
     * todavía), o la Response de error si falló.
     */
    protected function validateOrFail(Response $response, callable $validate): ?Response
    {
        try {
            $validate();
            return null;
        } catch (ValidationException $e) {
            return $this->jsonErrors($response, $e->getErrors());
        }
    }
}
