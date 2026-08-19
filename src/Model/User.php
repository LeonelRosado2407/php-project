<?php

declare(strict_types=1);

namespace Keel\Model;

/**
 * Class User
 * @package Keel\Model
 */
class User
{
    public function __construct(
        public readonly int $id,
        public readonly string $email,
        public readonly string $passwordHash,
        public readonly string $name,
    )
    {}
}