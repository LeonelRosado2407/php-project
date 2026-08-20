<?php

declare(strict_types=1);

namespace Keel\Database\Schema;

class ColumnDefinition
{
    private bool $nullable = false;
    private bool $hasDefault = false;
    private mixed $default = null;
    private bool $isChange = false;

    public function __construct(
        private string $name,
        private string $type, // ej. "VARCHAR(255)" o "INT AUTO_INCREMENT PRIMARY KEY"
    ) {}

    public function nullable(bool $value = true): self
    {
        $this->nullable = $value;
        return $this;
    }

    public function default(mixed $value): self
    {
        $this->hasDefault = true;
        $this->default = $value;
        return $this;
    }

    /**
     * Marca esta columna para usarse como MODIFY COLUMN en vez de ADD COLUMN
     * dentro de SchemaBuilder::table() (modo alter). Se re-declara la columna
     * con el mismo método usado en create(), ej. $table->string('email', 191)->change();
     */
    public function change(): self
    {
        $this->isChange = true;
        return $this;
    }

    public function isChange(): bool
    {
        return $this->isChange;
    }

    public function toSql(): string
    {
        $sql = "{$this->name} {$this->type}";
        $sql .= $this->nullable ? ' NULL' : ' NOT NULL';

        if ($this->hasDefault) {
            $sql .= ' DEFAULT ' . $this->formatDefault($this->default);
        }

        return $sql;
    }

    private function formatDefault(mixed $value): string
    {
        return match (true) {
            $value === null => 'NULL',
            is_bool($value) => $value ? '1' : '0',
            is_int($value) || is_float($value) => (string) $value,
            // Permite pasar expresiones SQL crudas como CURRENT_TIMESTAMP
            is_string($value) && str_starts_with($value, 'CURRENT_TIMESTAMP') => $value,
            default => "'" . addslashes((string) $value) . "'",
        };
    }
}