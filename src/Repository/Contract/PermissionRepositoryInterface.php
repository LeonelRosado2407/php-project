<?php

declare(strict_types=1);

namespace Keel\Repository\Contract;

interface PermissionRepositoryInterface
{
    /** @return string[] */
    public function getPermissionNamesForUser(int $userId): array;
}
