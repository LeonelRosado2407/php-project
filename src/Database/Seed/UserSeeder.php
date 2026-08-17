<?php

declare(strict_types=1);

namespace App\Database\Seed;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Un usuario fijo, para que siempre puedas loguear con credenciales conocidas
        $this->createUser('admin@test.com', 'password123', 'Admin');

        // 20 usuarios random con Faker
        for ($i = 0; $i < 20; $i++) {
            $this->createUser(
                $this->faker->unique()->safeEmail(),
                'password123',
                $this->faker->name(),
            );
        }

        echo "UserSeeder: 21 usuarios creados.\n";
    }

    private function createUser(string $email, string $password, string $name): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (email, password, name) VALUES (?, ?, ?)'
        );
        $stmt->execute([
            $email,
            password_hash($password, PASSWORD_DEFAULT),
            $name,
        ]);
    }
}