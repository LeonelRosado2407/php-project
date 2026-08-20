<?php

declare(strict_types=1);

namespace Tests\Unit\Database\Schema;

use PHPUnit\Framework\TestCase;
use Keel\Database\Schema\Blueprint;

class BlueprintTest extends TestCase
{
    // --- toCreateSql() ---

    public function test_create_incluye_columnas_simples(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->string('name');
        $blueprint->integer('age');

        $sql = $blueprint->toSql();

        $this->assertStringContainsString('CREATE TABLE users (', $sql);
        $this->assertStringContainsString('name VARCHAR(255) NOT NULL', $sql);
        $this->assertStringContainsString('age INT NOT NULL', $sql);
        $this->assertStringEndsWith(
            ') ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;',
            $sql
        );
    }

    public function test_create_incluye_id_y_timestamps(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->id();
        $blueprint->timestamps();

        $sql = $blueprint->toSql();

        $this->assertStringContainsString('id INT AUTO_INCREMENT PRIMARY KEY NOT NULL', $sql);
        $this->assertStringContainsString('created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP', $sql);
        $this->assertStringContainsString(
            'updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            $sql
        );
    }

    public function test_create_index_con_nombre_autogenerado(): void
    {
        $blueprint = new Blueprint('posts');
        $blueprint->string('slug');
        $blueprint->index('slug');

        $sql = $blueprint->toSql();

        $this->assertStringContainsString('INDEX posts_slug_index (slug)', $sql);
    }

    public function test_create_index_con_nombre_custom(): void
    {
        $blueprint = new Blueprint('posts');
        $blueprint->string('slug');
        $blueprint->index('slug', 'idx_slug_custom');

        $sql = $blueprint->toSql();

        $this->assertStringContainsString('INDEX idx_slug_custom (slug)', $sql);
    }

    public function test_create_unique_con_nombre_autogenerado(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->string('email');
        $blueprint->unique('email');

        $sql = $blueprint->toSql();

        $this->assertStringContainsString('UNIQUE users_email_unique (email)', $sql);
    }

    public function test_create_unique_con_nombre_custom(): void
    {
        $blueprint = new Blueprint('users');
        $blueprint->string('email');
        $blueprint->unique('email', 'uq_email_custom');

        $sql = $blueprint->toSql();

        $this->assertStringContainsString('UNIQUE uq_email_custom (email)', $sql);
    }

    public function test_create_index_compuesto(): void
    {
        $blueprint = new Blueprint('posts');
        $blueprint->integer('user_id');
        $blueprint->string('slug');
        $blueprint->unique(['user_id', 'slug']);

        $sql = $blueprint->toSql();

        $this->assertStringContainsString('UNIQUE posts_user_id_slug_unique (user_id, slug)', $sql);
    }

    public function test_create_foreign_encadenado(): void
    {
        $blueprint = new Blueprint('posts');
        $blueprint->integer('user_id');
        $blueprint->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

        $sql = $blueprint->toSql();

        $this->assertStringContainsString(
            'CONSTRAINT posts_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE',
            $sql
        );
    }

    public function test_create_engine_charset_collation_personalizados(): void
    {
        $blueprint = new Blueprint('logs');
        $blueprint->text('message');
        $blueprint->engine('MyISAM')->charset('utf8')->collation('utf8_general_ci');

        $sql = $blueprint->toSql();

        $this->assertStringContainsString('ENGINE=MyISAM', $sql);
        $this->assertStringContainsString('CHARSET=utf8', $sql);
        $this->assertStringContainsString('COLLATE=utf8_general_ci', $sql);
    }

    public function test_enum_con_array_vacio_lanza_invalid_argument_exception(): void
    {
        $blueprint = new Blueprint('posts');

        $this->expectException(\InvalidArgumentException::class);

        $blueprint->enum('status', []);
    }

    public function test_enum_con_valores(): void
    {
        $blueprint = new Blueprint('posts');
        $blueprint->enum('status', ['draft', 'published']);

        $sql = $blueprint->toSql();

        $this->assertStringContainsString("status ENUM('draft', 'published') NOT NULL", $sql);
    }

    // --- toAlterSql() ---

    public function test_alter_add_column(): void
    {
        $blueprint = new Blueprint('users', isAlter: true);
        $blueprint->string('nickname');

        $sql = $blueprint->toSql();

        $this->assertStringContainsString('ALTER TABLE users', $sql);
        $this->assertStringContainsString('ADD COLUMN nickname VARCHAR(255) NOT NULL', $sql);
        $this->assertStringEndsWith(';', $sql);
    }

    public function test_alter_modify_column_con_change(): void
    {
        $blueprint = new Blueprint('users', isAlter: true);
        $blueprint->string('email', 191)->change();

        $sql = $blueprint->toSql();

        $this->assertStringContainsString('MODIFY COLUMN email VARCHAR(191) NOT NULL', $sql);
        $this->assertStringNotContainsString('ADD COLUMN email', $sql);
    }

    public function test_alter_drop_column(): void
    {
        $blueprint = new Blueprint('users', isAlter: true);
        $blueprint->dropColumn('legacy_field');

        $sql = $blueprint->toSql();

        $this->assertStringContainsString('DROP COLUMN legacy_field', $sql);
    }

    public function test_alter_rename_column(): void
    {
        $blueprint = new Blueprint('users', isAlter: true);
        $blueprint->renameColumn('old_name', 'new_name');

        $sql = $blueprint->toSql();

        $this->assertStringContainsString('RENAME COLUMN old_name TO new_name', $sql);
    }

    public function test_alter_drop_index(): void
    {
        $blueprint = new Blueprint('users', isAlter: true);
        $blueprint->dropIndex('users_email_unique');

        $sql = $blueprint->toSql();

        $this->assertStringContainsString('DROP INDEX users_email_unique', $sql);
    }

    public function test_alter_drop_foreign(): void
    {
        $blueprint = new Blueprint('posts', isAlter: true);
        $blueprint->dropForeign('posts_user_id_foreign');

        $sql = $blueprint->toSql();

        $this->assertStringContainsString('DROP FOREIGN KEY posts_user_id_foreign', $sql);
    }

    public function test_alter_add_index_y_unique(): void
    {
        $blueprint = new Blueprint('users', isAlter: true);
        $blueprint->index('created_at');
        $blueprint->unique('email');

        $sql = $blueprint->toSql();

        $this->assertStringContainsString('ADD INDEX users_created_at_index (created_at)', $sql);
        $this->assertStringContainsString('ADD UNIQUE users_email_unique (email)', $sql);
    }

    public function test_alter_add_foreign_key(): void
    {
        $blueprint = new Blueprint('posts', isAlter: true);
        $blueprint->foreign('user_id')->references('id')->on('users');

        $sql = $blueprint->toSql();

        $this->assertStringContainsString(
            'ADD CONSTRAINT posts_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id)',
            $sql
        );
    }

    public function test_alter_combina_multiples_clausulas_en_orden(): void
    {
        $blueprint = new Blueprint('users', isAlter: true);
        $blueprint->string('nickname');
        $blueprint->string('email', 191)->change();
        $blueprint->renameColumn('old_name', 'new_name');
        $blueprint->index('created_at');
        $blueprint->dropIndex('users_legacy_index');
        $blueprint->foreign('role_id')->references('id')->on('roles');
        $blueprint->dropForeign('users_old_foreign');
        $blueprint->dropColumn('legacy_field');

        $sql = $blueprint->toSql();

        $expected = "ALTER TABLE users\n    "
            . "ADD COLUMN nickname VARCHAR(255) NOT NULL,\n    "
            . "MODIFY COLUMN email VARCHAR(191) NOT NULL,\n    "
            . "RENAME COLUMN old_name TO new_name,\n    "
            . "ADD INDEX users_created_at_index (created_at),\n    "
            . "DROP INDEX users_legacy_index,\n    "
            . "ADD CONSTRAINT users_role_id_foreign FOREIGN KEY (role_id) REFERENCES roles (id),\n    "
            . "DROP FOREIGN KEY users_old_foreign,\n    "
            . "DROP COLUMN legacy_field;";

        $this->assertSame($expected, $sql);
    }

    public function test_alter_sin_cambios_lanza_logic_exception(): void
    {
        $blueprint = new Blueprint('users', isAlter: true);

        $this->expectException(\LogicException::class);

        $blueprint->toSql();
    }
}
