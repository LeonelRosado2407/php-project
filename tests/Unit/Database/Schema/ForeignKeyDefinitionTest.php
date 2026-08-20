<?php

declare(strict_types=1);

namespace Tests\Unit\Database\Schema;

use PHPUnit\Framework\TestCase;
use Keel\Database\Schema\ForeignKeyDefinition;

class ForeignKeyDefinitionTest extends TestCase
{
    public function test_toSql_completo_con_references_y_on(): void
    {
        $fk = new ForeignKeyDefinition('posts_user_id_foreign', 'user_id');
        $fk->references('id')->on('users');

        $this->assertSame(
            'CONSTRAINT posts_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id)',
            $fk->toSql()
        );
    }

    public function test_toSql_con_onDelete_y_onUpdate(): void
    {
        $fk = new ForeignKeyDefinition('posts_user_id_foreign', 'user_id');
        $fk->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');

        $this->assertSame(
            'CONSTRAINT posts_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) '
                . 'ON DELETE CASCADE ON UPDATE CASCADE',
            $fk->toSql()
        );
    }

    public function test_onDelete_normaliza_guion_bajo_y_mayusculas(): void
    {
        $fk = new ForeignKeyDefinition('posts_user_id_foreign', 'user_id');
        $fk->references('id')->on('users')->onDelete('set_null');

        $this->assertSame(
            'CONSTRAINT posts_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) '
                . 'ON DELETE SET NULL',
            $fk->toSql()
        );
    }

    public function test_onUpdate_acepta_mayusculas_mixtas(): void
    {
        $fk = new ForeignKeyDefinition('posts_user_id_foreign', 'user_id');
        $fk->references('id')->on('users')->onUpdate('ReStRiCt');

        $this->assertSame(
            'CONSTRAINT posts_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id) '
                . 'ON UPDATE RESTRICT',
            $fk->toSql()
        );
    }

    public function test_referencedColumn_por_defecto_es_id(): void
    {
        $fk = new ForeignKeyDefinition('posts_user_id_foreign', 'user_id');
        $fk->on('users');

        $this->assertSame(
            'CONSTRAINT posts_user_id_foreign FOREIGN KEY (user_id) REFERENCES users (id)',
            $fk->toSql()
        );
    }

    public function test_accion_invalida_lanza_invalid_argument_exception(): void
    {
        $fk = new ForeignKeyDefinition('posts_user_id_foreign', 'user_id');

        $this->expectException(\InvalidArgumentException::class);

        $fk->onDelete('borrar_todo');
    }

    public function test_falta_on_lanza_logic_exception(): void
    {
        $fk = new ForeignKeyDefinition('posts_user_id_foreign', 'user_id');
        $fk->references('id');

        $this->expectException(\LogicException::class);

        $fk->toSql();
    }
}
