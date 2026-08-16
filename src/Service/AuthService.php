<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\EmailAlreadyExistsException;
use App\Repository\Contract\UserRepositoryInterface;
use App\Model\User;
use App\Exception\InvalidCredentialsException;
use App\Exception\UserNotFoundException;

class AuthService
{
    public function __construct(private UserRepositoryInterface $userRepository) {}

    public function attempt(string $email, string $password): User
    {
        $user = $this->userRepository->findByEmail($email);

        if (!$user) {
            throw new UserNotFoundException();
        }

        if (!password_verify($password, $user->passwordHash)) {
            throw new InvalidCredentialsException();
        }

        return $user;
    }

    public function register(string $name, string $email, string $password): User
    {
        if ($this->userRepository->existsByEmail($email)) {
            throw new EmailAlreadyExistsException();
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        return $this->userRepository->create($email, $passwordHash, $name);
    }
}