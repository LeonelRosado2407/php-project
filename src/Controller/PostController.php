<?php

declare(strict_types=1);

namespace Keel\Controller;

use Keel\Http\JsonResponder;
use Keel\Repository\Contract\PostRepositoryInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PostController
{
    use JsonResponder;

    public function __construct(private PostRepositoryInterface $postRepository) {}

    public function destroy(Request $request, Response $response, array $args): Response
    {
        $id = (int) $args['id'];
        $post = $this->postRepository->findById($id);

        if (!$post) {
            return $this->jsonError($response, 'Post no encontrado', 404);
        }

        $this->postRepository->delete($id);

        return $this->json($response, ['message' => 'Post eliminado']);
    }
}
