<?php

declare(strict_types=1);

namespace Keel\Repository;

use Keel\Repository\Contract\PostRepositoryInterface;
use Keel\Model\Post;
use PDO;

class PostRepository implements PostRepositoryInterface
{
    public function __construct(private PDO $pdo) {}

    public function findById(int $id): ?Post
    {
        $stmt = $this->pdo->prepare('SELECT id, user_id, title FROM posts WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new Post(
            id: (int) $row['id'],
            userId: (int) $row['user_id'],
            title: $row['title'],
        );
    }

    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM posts WHERE id = ?');
        $stmt->execute([$id]);
    }
}
