<?php

declare(strict_types=1);

require __DIR__ . '/utils/parse-args.php';
require __DIR__ . '/utils/migration-validations.php';

[$positional, $flags] = parseArgs($argv);

$name = $positional[0] ?? null;

// validaciones para los comandos de validciones


if ($name === null) {
    fwrite(STDERR, "Uso: composer make:migration <nombre> [--table=nombre_tabla]\n");
    exit(1);
}


$migrationsDir = __DIR__ . '/../src/Database/Migration';
$existing = findExistingMigration($migrationsDir, $name);

if ($existing !== null && !isset($flags['force'])) {
    fwrite(STDERR, "Ya existe una migración '{$name}' en: " . basename($existing) . "\n");
    fwrite(STDERR, "Usa --force para crear una nueva de todos modos.\n");
    exit(1);
}

// El timestamp se genera aquí, DESPUÉS de la validación
$timestamp = date('Y_m_d_His');
$fileName = "{$timestamp}_{$name}.php";
$path = $migrationsDir . '/' . $fileName;

// Prioridad: si viene --table=X explícito, úsalo.
// Si no, intenta detectarlo del patrón create_X_table.
if (isset($flags['table'])) {
    $table = $flags['table'];
} elseif (preg_match('/^create_(.+)_table$/', $name, $matches)) {
    $table = $matches[1];
} else {
    $table = 'table_name';
}


$stub = <<<PHP
<?php

declare(strict_types=1);

use App\Database\Schema\Blueprint;
use App\Database\Schema\SchemaBuilder;

return new class
{
    public function up(SchemaBuilder \$schema): void
    {
        \$schema->create('{$table}', function (Blueprint \$table) {
            \$table->id();
            \$table->timestamps();
        });
    }

    public function down(SchemaBuilder \$schema): void
    {
        \$schema->drop('{$table}');
    }
};

PHP;

file_put_contents($path, $stub);

echo "Migración creada: {$fileName}\n";