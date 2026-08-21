<?php

declare(strict_types=1);

namespace Keel\Database\Seed;

use Faker\Factory;
use Faker\Generator;
use PDO;

abstract class Seeder
{
    protected Generator $faker;

    public function __construct(protected PDO $pdo)
    {
        $this->faker = Factory::create(); // por defecto en inglés
        // $this->faker = Factory::create('es_MX'); // si prefieres datos en español/México
    }

    abstract public function run(): void;
}