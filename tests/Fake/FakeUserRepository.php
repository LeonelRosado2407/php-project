<?php

declare(strict_types=1);

namespace Tests\Fake;

use Keel\Repository\Contract\UserRepositoryInterface;
use Keel\Model\User;

class FakeUserRepository implements UserRepositoryInterface
{
    /** @var User[] */
    private array $createdUsers = [];

    public function __construct(
        private ?User $userToReturn = null,
        private bool $emailExists = false,
    ) {}

    public function findByEmail(string $email): ?User
    {
        return $this->userToReturn;
    }

    public function existsByEmail(string $email): bool
    {
        return $this->emailExists;
    }

    public function create(string $email, string $passwordHash, string $name): User
    {
        $user = new User(
            id: count($this->createdUsers) + 1,
            email: $email,
            passwordHash: $passwordHash,
            name: $name,
        );

        $this->createdUsers[] = $user;

        return $user;
    }

    /** @return User[] */
    public function getCreatedUsers(): array
    {
        return $this->createdUsers;
    }
}