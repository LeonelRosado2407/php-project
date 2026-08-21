<?php

declare(strict_types=1);

use Keel\Database\Schema\Blueprint;
use Keel\Database\Schema\SchemaBuilder;

return new class
{
    public function up(SchemaBuilder $schema): void
    {
        $schema->create('user_permission', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('permission_id');
            $table->unique(['user_id', 'permission_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
        });
    }

    public function down(SchemaBuilder $schema): void
    {
        $schema->drop('user_permission');
    }
};
