<?php

declare(strict_types=1);

namespace Tests\Fake;

use Keel\Repository\Contract\PermissionRepositoryInterface;

class FakePermissionRepository implements PermissionRepositoryInterface
{
    /** @param array<int, string[]> $permissionNamesByUserId */
    public function __construct(private array $permissionNamesByUserId = []) {}

    public function getPermissionNamesForUser(int $userId): array
    {
        return $this->permissionNamesByUserId[$userId] ?? [];
    }
}
