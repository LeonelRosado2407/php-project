<?php

declare(strict_types=1);

namespace Keel\Database\Schema;

use PDO;

class SchemaBuilder
{
    public function __construct(private PDO $pdo) {}

    public function create(string $table, callable $callback): void
    {
        $blueprint = new Blueprint($table);
        $callback($blueprint);
        $this->pdo->exec($blueprint->toSql());
    }

    public function table(string $table, callable $callback): void
    {
        $blueprint = new Blueprint($table, isAlter: true);
        $callback($blueprint);
        $this->pdo->exec($blueprint->toSql());
    }

    public function drop(string $table): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS {$table}");
    }
}