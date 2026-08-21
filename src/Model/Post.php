<?php

declare(strict_types=1);

namespace Keel\Model;

/**
 * Class Post
 * @package Keel\Model
 */
class Post
{
    public function __construct(
        public readonly int $id,
        public readonly int $userId,
        public readonly string $title,
    )
    {}
}
