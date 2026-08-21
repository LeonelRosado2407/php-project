<?php

declare(strict_types=1);

use Keel\Database\Schema\Blueprint;
use Keel\Database\Schema\SchemaBuilder;

return new class
{
    public function up(SchemaBuilder $schema): void
    {
        $schema->create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('password');
            $table->string('name');
            $table->unique('email');
            $table->timestamps();
        });
    }

    public function down(SchemaBuilder $schema): void
    {
        $schema->drop('users');
    }
};