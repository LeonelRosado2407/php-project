<?php

declare(strict_types=1);

namespace Keel\Database\Schema;

use InvalidArgumentException;
use LogicException;

class ForeignKeyDefinition
{
    private string $referencedColumn = 'id';
    private ?string $referencedTable = null;
    private ?string $onDeleteAction = null;
    private ?string $onUpdateAction = null;

    public function __construct(
        private string $name,
        private string $column,
    ) {}

    public function references(string $column): self
    {
        $this->referencedColumn = $column;
        return $this;
    }

    public function on(string $table): self
    {
        $this->referencedTable = $table;
        return $this;
    }

    public function onDelete(string $action): self
    {
        $this->onDeleteAction = $this->normalizeAction($action);
        return $this;
    }

    public function onUpdate(string $action): self
    {
        $this->onUpdateAction = $this->normalizeAction($action);
        return $this;
    }

    public function toSql(): string
    {
        if ($this->referencedTable === null) {
            throw new LogicException(
                "La foreign key '{$this->name}' no tiene tabla referenciada. Usa ->on('tabla')."
            );
        }

        $sql = "CONSTRAINT {$this->name} FOREIGN KEY ({$this->column}) "
            . "REFERENCES {$this->referencedTable} ({$this->referencedColumn})";

        if ($this->onDeleteAction !== null) {
            $sql .= " ON DELETE {$this->onDeleteAction}";
        }

        if ($this->onUpdateAction !== null) {
            $sql .= " ON UPDATE {$this->onUpdateAction}";
        }

        return $sql;
    }

    private function normalizeAction(string $action): string
    {
        $action = strtoupper(str_replace('_', ' ', $action));
        $valid = ['CASCADE', 'SET NULL', 'RESTRICT', 'NO ACTION'];

        if (!in_array($action, $valid, true)) {
            throw new InvalidArgumentException("Acción de foreign key inválida: {$action}");
        }

        return $action;
    }
}