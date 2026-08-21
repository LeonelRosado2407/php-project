<?php

declare(strict_types=1);

namespace Keel\Repository;

use Keel\Repository\Contract\UserRepositoryInterface;
use Keel\Model\User;
use PDO;

class UserRepository implements UserRepositoryInterface
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

    public function existsByEmail(string $email): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM users WHERE email = ?');
        $stmt->execute([$email]);

        return (bool) $stmt->fetchColumn();
    }

    public function create(string $email, string $passwordHash, string $name): User
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (email, password, name) VALUES (?, ?, ?)'
        );
        $stmt->execute([$email, $passwordHash, $name]);

        $id = (int) $this->pdo->lastInsertId();

        return new User(
            id: $id,
            email: $email,
            passwordHash: $passwordHash,
            name: $name,
        );
    }
}