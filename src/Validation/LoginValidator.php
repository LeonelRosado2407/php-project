<?php

declare(strict_types=1);

namespace App\Validation;

use App\DTO\LoginRequestDTO;
use App\Exception\ValidationException;

class LoginValidator
{
    public function validate(LoginRequestDTO $dto): void
    {
        $errors = [];

        if (empty($dto->email)) {
            $errors['email'] = 'El email es requerido';
        } elseif (!filter_var($dto->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'El formato de email no es válido';
        }

        if (empty($dto->password)) {
            $errors['password'] = 'La contraseña es requerida';
        } elseif (strlen($dto->password) < 6) {
            $errors['password'] = 'La contraseña debe tener al menos 6 caracteres';
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }
}