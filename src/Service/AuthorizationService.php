<?php

declare(strict_types=1);

namespace Keel\Service;

use Keel\Repository\Contract\PermissionRepositoryInterface;

class AuthorizationService
{
    public function __construct(private PermissionRepositoryInterface $permissionRepository) {}

    public function can(int $userId, string $permission): bool
    {
        return in_array(
            $permission,
            $this->permissionRepository->getPermissionNamesForUser($userId),
            true
        );
    }
}
