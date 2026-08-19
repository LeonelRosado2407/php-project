<?php

declare(strict_types=1);

use App\Database\Schema\Blueprint;
use App\Database\Schema\SchemaBuilder;

return new class
{
    public function up(SchemaBuilder $schema): void
    {
        $schema->create('posts', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('title');
            $table->text('body');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->decimal('reading_time_minutes', 4, 1)->nullable();
            $table->json('metadata')->nullable();
            $table->date('published_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(SchemaBuilder $schema): void
    {
        $schema->drop('posts');
    }
};
