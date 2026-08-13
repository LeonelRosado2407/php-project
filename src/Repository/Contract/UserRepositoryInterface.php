<?php

declare(strict_types=1);

namespace App\Repository\Contract;

use App\Model\User;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?User;
}