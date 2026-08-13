<?php

declare(strict_types=1);

namespace Tests\Fake;

use App\Repository\Contract\UserRepositoryInterface;
use App\Model\User;

class FakeUserRepository implements UserRepositoryInterface
{
    public function __construct(private ?User $userToReturn = null) {}

    public function findByEmail(string $email): ?User
    {
        return $this->userToReturn;
    }
}