<?php

declare(strict_types=1);

namespace Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Keel\Service\AuthService;
use Keel\Model\User;
use Keel\Exception\UserNotFoundException;
use Keel\Exception\InvalidCredentialsException;
use Tests\Fake\FakeUserRepository;

class AuthServiceTest extends TestCase
{
    public function test_attempt_regresa_usuario_con_credenciales_correctas(): void
    {
        $user = new User(
            id: 1,
            email: 'test@test.com',
            passwordHash: password_hash('secret123', PASSWORD_DEFAULT),
            name: 'Leonel',
        );

        $repository = new FakeUserRepository($user);
        $service = new AuthService($repository);

        $result = $service->attempt('test@test.com', 'secret123');

        $this->assertSame($user, $result);
    }

    public function test_attempt_lanza_excepcion_si_usuario_no_existe(): void
    {
        $repository = new FakeUserRepository(null); // simula que no lo encontró
        $service = new AuthService($repository);

        $this->expectException(UserNotFoundException::class);

        $service->attempt('noexiste@test.com', 'cualquiera');
    }

    public function test_attempt_lanza_excepcion_si_password_es_incorrecto(): void
    {
        $user = new User(
            id: 1,
            email: 'test@test.com',
            passwordHash: password_hash('secret123', PASSWORD_DEFAULT),
            name: 'Leonel',
        );

        $repository = new FakeUserRepository($user);
        $service = new AuthService($repository);

        $this->expectException(InvalidCredentialsException::class);

        $service->attempt('test@test.com', 'password-incorrecto');
    }
}