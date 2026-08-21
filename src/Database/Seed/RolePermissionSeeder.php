<?php

declare(strict_types=1);

namespace Keel\Database\Seed;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissionIds = [];

        foreach (['posts.create', 'posts.edit', 'posts.delete'] as $name) {
            $stmt = $this->pdo->prepare('INSERT INTO permissions (name) VALUES (?)');
            $stmt->execute([$name]);
            $permissionIds[$name] = (int) $this->pdo->lastInsertId();
        }

        $adminRoleId = $this->createRole('admin');
        $this->createRole('user');

        foreach ($permissionIds as $permissionId) {
            $stmt = $this->pdo->prepare(
                'INSERT INTO role_permission (role_id, permission_id) VALUES (?, ?)'
            );
            $stmt->execute([$adminRoleId, $permissionId]);
        }

        echo "RolePermissionSeeder: 2 roles, 3 permisos, admin con todos los permisos.\n";
    }

    private function createRole(string $name): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO roles (name) VALUES (?)');
        $stmt->execute([$name]);

        return (int) $this->pdo->lastInsertId();
    }
}
