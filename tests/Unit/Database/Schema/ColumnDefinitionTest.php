<?php

declare(strict_types=1);

namespace Tests\Unit\Database\Schema;

use PHPUnit\Framework\TestCase;
use Keel\Database\Schema\ColumnDefinition;

class ColumnDefinitionTest extends TestCase
{
    public function test_toSql_es_not_null_por_defecto(): void
    {
        $column = new ColumnDefinition('email', 'VARCHAR(255)');

        $this->assertSame('email VARCHAR(255) NOT NULL', $column->toSql());
    }

    public function test_nullable_marca_la_columna_como_null(): void
    {
        $column = new ColumnDefinition('bio', 'TEXT');
        $column->nullable();

        $this->assertSame('bio TEXT NULL', $column->toSql());
    }

    public function test_nullable_false_vuelve_a_marcar_not_null(): void
    {
        $column = new ColumnDefinition('bio', 'TEXT');
        $column->nullable(true);
        $column->nullable(false);

        $this->assertSame('bio TEXT NOT NULL', $column->toSql());
    }

    public function test_default_null(): void
    {
        $column = new ColumnDefinition('deleted_at', 'DATETIME');
        $column->nullable()->default(null);

        $this->assertSame('deleted_at DATETIME NULL DEFAULT NULL', $column->toSql());
    }

    public function test_default_bool_true(): void
    {
        $column = new ColumnDefinition('active', 'TINYINT(1)');
        $column->default(true);

        $this->assertSame('active TINYINT(1) NOT NULL DEFAULT 1', $column->toSql());
    }

    public function test_default_bool_false(): void
    {
        $column = new ColumnDefinition('active', 'TINYINT(1)');
        $column->default(false);

        $this->assertSame('active TINYINT(1) NOT NULL DEFAULT 0', $column->toSql());
    }

    public function test_default_int(): void
    {
        $column = new ColumnDefinition('stock', 'INT');
        $column->default(0);

        $this->assertSame('stock INT NOT NULL DEFAULT 0', $column->toSql());
    }

    public function test_default_float(): void
    {
        $column = new ColumnDefinition('price', 'DECIMAL(8, 2)');
        $column->default(9.99);

        $this->assertSame('price DECIMAL(8, 2) NOT NULL DEFAULT 9.99', $column->toSql());
    }

    public function test_default_string_escapa_comillas(): void
    {
        $column = new ColumnDefinition('name', 'VARCHAR(255)');
        $column->default("O'Reilly");

        $this->assertSame("name VARCHAR(255) NOT NULL DEFAULT 'O\\'Reilly'", $column->toSql());
    }

    public function test_default_current_timestamp_va_crudo_sin_comillas(): void
    {
        $column = new ColumnDefinition('created_at', 'DATETIME');
        $column->default('CURRENT_TIMESTAMP');

        $this->assertSame('created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP', $column->toSql());
    }

    public function test_default_current_timestamp_on_update_va_crudo(): void
    {
        $column = new ColumnDefinition('updated_at', 'DATETIME');
        $column->default('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');

        $this->assertSame(
            'updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            $column->toSql()
        );
    }

    public function test_change_marca_isChange_como_true(): void
    {
        $column = new ColumnDefinition('email', 'VARCHAR(191)');

        $this->assertFalse($column->isChange());

        $result = $column->change();

        $this->assertTrue($column->isChange());
        $this->assertSame($column, $result);
    }
}
