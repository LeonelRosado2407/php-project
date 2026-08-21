<?php

declare(strict_types=1);

namespace Tests\Unit\Service;

use PHPUnit\Framework\TestCase;
use Keel\Service\AuthorizationService;
use Tests\Fake\FakePermissionRepository;

class AuthorizationServiceTest extends TestCase
{
    public function test_can_devuelve_true_si_el_usuario_tiene_el_permiso(): void
    {
        $repository = new FakePermissionRepository([1 => ['posts.delete', 'posts.edit']]);
        $service = new AuthorizationService($repository);

        $this->assertTrue($service->can(1, 'posts.delete'));
    }

    public function test_can_devuelve_false_si_el_usuario_no_tiene_el_permiso(): void
    {
        $repository = new FakePermissionRepository([1 => ['posts.edit']]);
        $service = new AuthorizationService($repository);

        $this->assertFalse($service->can(1, 'posts.delete'));
    }

    public function test_can_devuelve_false_si_el_usuario_no_tiene_ningun_permiso(): void
    {
        $repository = new FakePermissionRepository([]);
        $service = new AuthorizationService($repository);

        $this->assertFalse($service->can(1, 'posts.delete'));
    }

    public function test_can_distingue_permisos_entre_usuarios_distintos(): void
    {
        $repository = new FakePermissionRepository([
            1 => ['posts.delete'],
            2 => [],
        ]);
        $service = new AuthorizationService($repository);

        $this->assertTrue($service->can(1, 'posts.delete'));
        $this->assertFalse($service->can(2, 'posts.delete'));
    }
}
