<?php

declare(strict_types=1);

namespace Tests\Unit\Validation;

use PHPUnit\Framework\TestCase;
use Keel\Validation\LoginValidator;
use Keel\DTO\LoginRequestDTO;
use Keel\Exception\ValidationException;

class LoginValidatorTest extends TestCase
{
    public function test_falla_si_email_esta_vacio(): void
    {
        $validator = new LoginValidator();
        $dto = new LoginRequestDTO(email: '', password: 'secret123');

        $this->expectException(ValidationException::class);

        $validator->validate($dto);
    }

    public function test_pasa_con_datos_validos(): void
    {
        $validator = new LoginValidator();
        $dto = new LoginRequestDTO(email: 'test@test.com', password: 'secret123');

        $this->expectNotToPerformAssertions();

        $validator->validate($dto); // no debería lanzar nada
    }
}