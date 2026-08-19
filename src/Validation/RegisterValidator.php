<?php

declare(strict_types=1);

namespace Keel\Validation;

use Keel\DTO\RegisterRequestDTO;
use Keel\Exception\ValidationException;

class RegisterValidator
{
    public function validate(RegisterRequestDTO $dto): void
    {
        $errors = [];

        // TODO: valida $dto->name (no vacío, quizás longitud mínima)
        if (empty($dto->name)) {
            $errors['name'] = 'El nombre es requerido';
        } elseif (strlen($dto->name) < 3) {
            $errors['name'] = 'El nombre debe tener al menos 3 caracteres';
        }
        // TODO: valida $dto->email (no vacío + formato válido, como en LoginValidator)
        if (empty($dto->email)) {
            $errors['email'] = 'El correo electrónico es requerido';
        } elseif (!filter_var($dto->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'El correo electrónico no es válido';
        }
        // TODO: valida $dto->password (no vacío + longitud mínima, como en LoginValidator)
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