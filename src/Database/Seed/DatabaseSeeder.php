<?php

declare(strict_types=1);

namespace Keel\Database\Seed;

use PDO;

class DatabaseSeeder
{
    public function __construct(private PDO $pdo) {}

    public function run(): void
    {
        $seeders = [
            UserSeeder::class,
            RolePermissionSeeder::class,
            // PostSeeder::class,  ← aquí agregas más, en el orden que necesites
        ];

        foreach ($seeders as $seederClass) {
            $seeder = new $seederClass($this->pdo);
            $seeder->run();
        }
    }
}