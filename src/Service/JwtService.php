<?php

declare(strict_types=1);

namespace App\Service;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

class JwtService
{
    public function __construct(
        private string $secret,
        private int $expiration
    ) {}

    public function generate(int $userId): string
    {
        $payload = [
            'iat' => time(),                    // issued at (cuándo se creó)
            'exp' => time() + $this->expiration, // expiration (cuándo expira)
            'sub' => $userId,                    // subject (a quién representa el token)
        ];

        return JWT::encode($payload, $this->secret, 'HS256');
    }

    public function verify(string $token): ?int
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, 'HS256'));
            return (int) $decoded->sub;
        } catch (ExpiredException $e) {
            return null; // token expiró
        } catch (\Exception $e) {
            return null; // token inválido/manipulado
        }
    }
}