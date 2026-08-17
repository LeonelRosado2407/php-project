<?php

declare(strict_types=1);

namespace App\Database\Schema;

class Blueprint
{
    private string $table;
    private array $columns = [];
    private array $indexes = [];

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public function id(string $name = 'id'): self
    {
        $this->columns[] = "{$name} INT AUTO_INCREMENT PRIMARY KEY";
        return $this;
    }

    public function string(string $name, int $length = 255): self
    {
        $this->columns[] = "{$name} VARCHAR({$length}) NOT NULL";
        return $this;
    }

    public function text(string $name): self
    {
        $this->columns[] = "{$name} TEXT NOT NULL";
        return $this;
    }

    public function integer(string $name): self
    {
        $this->columns[] = "{$name} INT NOT NULL";
        return $this;
    }

    public function boolean(string $name): self
    {
        $this->columns[] = "{$name} TINYINT(1) NOT NULL DEFAULT 0";
        return $this;
    }

    public function timestamps(): self
    {
        $this->columns[] = "created_at DATETIME DEFAULT CURRENT_TIMESTAMP";
        $this->columns[] = "updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
        return $this;
    }

    public function unique(string $column): self
    {
        $this->indexes[] = "UNIQUE ({$column})";
        return $this;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function toSql(): string
    {
        $definitions = array_merge($this->columns, $this->indexes);
        $definitionsSql = implode(",\n    ", $definitions);

        return "CREATE TABLE {$this->table} (\n    {$definitionsSql}\n);";
    }
}