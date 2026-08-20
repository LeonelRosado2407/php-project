<?php

declare(strict_types=1);

namespace Keel\Repository;

use Keel\Repository\Contract\PermissionRepositoryInterface;
use PDO;

class PermissionRepository implements PermissionRepositoryInterface
{
    public function __construct(private PDO $pdo) {}

    public function getPermissionNamesForUser(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.name FROM permissions p
             JOIN role_permission rp ON rp.permission_id = p.id
             JOIN user_role ur ON ur.role_id = rp.role_id
             WHERE ur.user_id = ?
             UNION
             SELECT p.name FROM permissions p
             JOIN user_permission up ON up.permission_id = p.id
             WHERE up.user_id = ?'
        );
        $stmt->execute([$userId, $userId]);

        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
