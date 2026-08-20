<?php

declare(strict_types=1);

namespace Keel\Database\Schema;

class Blueprint
{
    private string $table;
    /** @var ColumnDefinition[] */
    private array $columns = [];
    private array $indexes = [];
    /** @var ForeignKeyDefinition[] */
    private array $foreignKeys = [];
    private array $dropColumns = [];
    private array $renameColumns = [];
    private array $dropIndexes = [];
    private array $dropForeignKeys = [];
    private string $engine = 'InnoDB';
    private string $charset = 'utf8mb4';
    private string $collation = 'utf8mb4_unicode_ci';

    public function __construct(string $table, private bool $isAlter = false)
    {
        $this->table = $table;
    }

    public function id(string $name = 'id'): ColumnDefinition
    {
        return $this->addColumn($name, 'INT AUTO_INCREMENT PRIMARY KEY');
    }

    public function string(string $name, int $length = 255): ColumnDefinition
    {
        return $this->addColumn($name, "VARCHAR({$length})");
    }

    public function text(string $name): ColumnDefinition
    {
        return $this->addColumn($name, 'TEXT');
    }

    public function integer(string $name): ColumnDefinition
    {
        return $this->addColumn($name, 'INT');
    }

    public function boolean(string $name): ColumnDefinition
    {
        return $this->addColumn($name, 'TINYINT(1)')->default(0);
    }

    public function bigInteger(string $name): ColumnDefinition
    {
        return $this->addColumn($name, 'BIGINT');
    }

    public function decimal(string $name, int $precision = 8, int $scale = 2): ColumnDefinition
    {
        return $this->addColumn($name, "DECIMAL({$precision}, {$scale})");
    }

    public function date(string $name): ColumnDefinition
    {
        return $this->addColumn($name, 'DATE');
    }

    public function dateTime(string $name): ColumnDefinition
    {
        return $this->addColumn($name, 'DATETIME');
    }

    /**
     * @param string[] $values Lista fija de valores permitidos, ej. ['draft', 'published', 'archived']
     */
    public function enum(string $name, array $values): ColumnDefinition
    {
        if ($values === []) {
            throw new \InvalidArgumentException("enum('{$name}') requiere al menos un valor.");
        }

        $quoted = array_map(fn (string $v) => "'" . addslashes($v) . "'", $values);
        $type = 'ENUM(' . implode(', ', $quoted) . ')';

        return $this->addColumn($name, $type);
    }

    public function json(string $name): ColumnDefinition
    {
        return $this->addColumn($name, 'JSON');
    }

    public function timestamps(): self
    {
        $this->addColumn('created_at', 'DATETIME')->default('CURRENT_TIMESTAMP');
        $this->addColumn('updated_at', 'DATETIME')->default('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP');
        return $this;
    }

    /**
     * Índice normal (no único). Acelera búsquedas/ordenamientos, no impone restricciones.
     *
     * @param string|string[] $columns Una columna o un arreglo para índice compuesto.
     */
    public function index(string|array $columns, ?string $name = null): self
    {
        $columns = (array) $columns;
        $name ??= $this->generateIndexName($columns, 'index');
        $this->indexes[] = "INDEX {$name} (" . implode(', ', $columns) . ')';
        return $this;
    }

    /**
     * Índice único. Igual que index(), pero además prohíbe duplicados
     * en la combinación de columnas dada.
     *
     * @param string|string[] $columns Una columna o un arreglo para índice compuesto.
     */
    public function unique(string|array $columns, ?string $name = null): self
    {
        $columns = (array) $columns;
        $name ??= $this->generateIndexName($columns, 'unique');
        $this->indexes[] = "UNIQUE {$name} (" . implode(', ', $columns) . ')';
        return $this;
    }

    private function generateIndexName(array $columns, string $type): string
    {
        return implode('_', [$this->table, ...$columns, $type]);
    }

    /**
     * Define una foreign key sobre $column. Requiere encadenar ->references()->on()
     * para completarla, y opcionalmente ->onDelete()/->onUpdate().
     *
     * Ejemplo: $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
     */
    public function foreign(string $column, ?string $name = null): ForeignKeyDefinition
    {
        $name ??= "{$this->table}_{$column}_foreign";
        $fk = new ForeignKeyDefinition($name, $column);
        $this->foreignKeys[] = $fk;
        return $fk;
    }

    /**
     * Sobreescribe el engine de la tabla (default: InnoDB). Solo aplica en create(),
     * ya que cambiar el engine de una tabla existente es una operación distinta.
     */
    public function engine(string $engine): self
    {
        $this->engine = $engine;
        return $this;
    }

    /** Sobreescribe el charset de la tabla (default: utf8mb4). Solo aplica en create(). */
    public function charset(string $charset): self
    {
        $this->charset = $charset;
        return $this;
    }

    /** Sobreescribe el collation de la tabla (default: utf8mb4_unicode_ci). Solo aplica en create(). */
    public function collation(string $collation): self
    {
        $this->collation = $collation;
        return $this;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Marca una columna para eliminarse. Solo tiene sentido dentro de
     * SchemaBuilder::table() (modo alter) — en create() no aplica.
     */
    public function dropColumn(string $name): self
    {
        $this->dropColumns[] = $name;
        return $this;
    }

    /**
     * Renombra una columna existente. Solo tiene sentido dentro de
     * SchemaBuilder::table() (modo alter) — en create() no aplica.
     * Usa sintaxis RENAME COLUMN de MySQL 8+.
     */
    public function renameColumn(string $from, string $to): self
    {
        $this->renameColumns[] = "RENAME COLUMN {$from} TO {$to}";
        return $this;
    }

    /**
     * Elimina un índice existente por nombre. Solo tiene sentido dentro de
     * SchemaBuilder::table() (modo alter) — en create() no aplica.
     */
    public function dropIndex(string $name): self
    {
        $this->dropIndexes[] = "DROP INDEX {$name}";
        return $this;
    }

    /**
     * Elimina una foreign key existente por nombre. Solo tiene sentido dentro de
     * SchemaBuilder::table() (modo alter) — en create() no aplica.
     */
    public function dropForeign(string $name): self
    {
        $this->dropForeignKeys[] = "DROP FOREIGN KEY {$name}";
        return $this;
    }

    public function toSql(): string
    {
        return $this->isAlter ? $this->toAlterSql() : $this->toCreateSql();
    }

    private function toCreateSql(): string
    {
        $columnsSql = array_map(fn (ColumnDefinition $c) => $c->toSql(), $this->columns);
        $foreignKeysSql = array_map(fn (ForeignKeyDefinition $f) => $f->toSql(), $this->foreignKeys);
        $definitions = array_merge($columnsSql, $this->indexes, $foreignKeysSql);
        $definitionsSql = implode(",\n    ", $definitions);

        // InnoDB es obligatorio en MySQL para que las FOREIGN KEY tengan efecto real.
        // utf8mb4 soporta Unicode completo (emojis incluidos); utf8 a secas no.
        return "CREATE TABLE {$this->table} (\n    {$definitionsSql}\n) "
            . "ENGINE={$this->engine} DEFAULT CHARSET={$this->charset} COLLATE={$this->collation};";
    }

    private function toAlterSql(): string
    {
        $clauses = [];

        foreach ($this->columns as $column) {
            $clauses[] = $column->isChange()
                ? "MODIFY COLUMN {$column->toSql()}"
                : "ADD COLUMN {$column->toSql()}";
        }

        foreach ($this->renameColumns as $renameColumn) {
            $clauses[] = $renameColumn;
        }

        foreach ($this->indexes as $index) {
            $clauses[] = "ADD {$index}";
        }

        foreach ($this->dropIndexes as $dropIndex) {
            $clauses[] = $dropIndex;
        }

        foreach ($this->foreignKeys as $fk) {
            $clauses[] = "ADD {$fk->toSql()}";
        }

        foreach ($this->dropForeignKeys as $dropForeign) {
            $clauses[] = $dropForeign;
        }

        foreach ($this->dropColumns as $name) {
            $clauses[] = "DROP COLUMN {$name}";
        }

        if ($clauses === []) {
            throw new \LogicException("No se definió ningún cambio para la tabla {$this->table}.");
        }

        $clausesSql = implode(",\n    ", $clauses);

        return "ALTER TABLE {$this->table}\n    {$clausesSql};";
    }

    private function addColumn(string $name, string $type): ColumnDefinition
    {
        $column = new ColumnDefinition($name, $type);
        $this->columns[] = $column;
        return $column;
    }
}