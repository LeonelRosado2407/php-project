<?php

declare(strict_types=1);

namespace Keel\Repository\Contract;

use Keel\Model\User;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?User;
    public function existsByEmail(string $email): bool;
    public function create(string $email, string $passwordHash, string $name): User;
}