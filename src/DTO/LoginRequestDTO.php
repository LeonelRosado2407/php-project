<?php

declare(strict_types=1);

namespace Keel\DTO;

class LoginRequestDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'] ?? '',
            password: $data['password'] ?? '',
        );
    }
}