<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\User;
use PDO;

class UserRepository
{
    public function __construct(private PDO $pdo) {}

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return new User(
            id: (int) $row['id'],
            email: $row['email'],
            passwordHash: $row['password'],
            name: $row['name'],
        );
    }
}