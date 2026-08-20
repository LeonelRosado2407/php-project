<?php

declare(strict_types=1);

namespace Tests\Unit\Database\Schema;

use PDO;
use PHPUnit\Framework\TestCase;
use Keel\Database\Schema\Blueprint;
use Keel\Database\Schema\SchemaBuilder;

class SchemaBuilderTest extends TestCase
{
    public function test_create_ejecuta_el_sql_de_creacion_via_pdo(): void
    {
        $expectedSql = "CREATE TABLE widgets (\n    name VARCHAR(255) NOT NULL\n) "
            . "ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->once())
            ->method('exec')
            ->with($expectedSql);

        $schema = new SchemaBuilder($pdo);
        $schema->create('widgets', function (Blueprint $table): void {
            $table->string('name');
        });
    }

    public function test_table_ejecuta_el_sql_de_alter_via_pdo(): void
    {
        $expectedSql = "ALTER TABLE widgets\n    DROP COLUMN legacy_field;";

        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->once())
            ->method('exec')
            ->with($expectedSql);

        $schema = new SchemaBuilder($pdo);
        $schema->table('widgets', function (Blueprint $table): void {
            $table->dropColumn('legacy_field');
        });
    }

    public function test_drop_ejecuta_drop_table_if_exists(): void
    {
        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->once())
            ->method('exec')
            ->with('DROP TABLE IF EXISTS widgets');

        $schema = new SchemaBuilder($pdo);
        $schema->drop('widgets');
    }

    public function test_rename_ejecuta_alter_table_rename_to(): void
    {
        $pdo = $this->createMock(PDO::class);
        $pdo->expects($this->once())
            ->method('exec')
            ->with('ALTER TABLE widgets RENAME TO gadgets;');

        $schema = new SchemaBuilder($pdo);
        $schema->rename('widgets', 'gadgets');
    }
}
