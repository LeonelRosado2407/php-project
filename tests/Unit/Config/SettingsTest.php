<?php

declare(strict_types=1);

namespace Tests\Unit\Config;

use PHPUnit\Framework\TestCase;
use Keel\Config\Settings;

class SettingsTest extends TestCase
{
    public function test_devuelve_valor_de_clave_simple(): void
    {
        $settings = new Settings(['app' => ['env' => 'production']]);

        $this->assertSame('production', $settings->get('app.env'));
    }

    public function test_devuelve_valor_de_clave_anidada_profunda(): void
    {
        $settings = new Settings(['db' => ['credentials' => ['user' => 'root']]]);

        $this->assertSame('root', $settings->get('db.credentials.user'));
    }

    public function test_devuelve_default_si_la_clave_no_existe(): void
    {
        $settings = new Settings(['app' => ['env' => 'production']]);

        $this->assertSame('fallback', $settings->get('app.debug', 'fallback'));
    }

    public function test_devuelve_null_por_defecto_si_no_hay_default_explicito(): void
    {
        $settings = new Settings([]);

        $this->assertNull($settings->get('no.existe'));
    }

    public function test_devuelve_default_si_un_segmento_intermedio_no_es_array(): void
    {
        $settings = new Settings(['app' => 'production']);

        $this->assertNull($settings->get('app.env'));
    }

    public function test_devuelve_un_array_completo_si_la_clave_apunta_a_uno(): void
    {
        $settings = new Settings(['db' => ['host' => 'localhost', 'port' => 3306]]);

        $this->assertSame(['host' => 'localhost', 'port' => 3306], $settings->get('db'));
    }
}
