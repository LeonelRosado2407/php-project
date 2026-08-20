<?php

declare(strict_types=1);

namespace Keel\Repository\Contract;

use Keel\Model\Post;

interface PostRepositoryInterface
{
    public function findById(int $id): ?Post;
    public function delete(int $id): void;
}
