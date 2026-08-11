<?php

declare(strict_types=1);

namespace App\Service;

use App\Repository\UserRepository;
use App\Model\User;

class AuthService
{
    public function __construct(private UserRepository $userRepository) {}

    public function attempt(string $email, string $password): ?User
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            return null;
        }

        if (!password_verify($password, $user->passwordHash)) {
            return null;
        }

        return $user;
    }
}