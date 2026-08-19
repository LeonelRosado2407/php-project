# php-framework

**Autor:** Leonel Rosado

## Objetivo

Proyecto para reforzar conocimientos de PHP y cómo se estructura un proyecto serio; para ello esto es una API REST construida con Slim4, sin ningún framework "todo en uno" — el contenedor de dependencias, el sistema de migraciones y el de seeders están hechos a mano para entender qué hace Laravel (y frameworks similares) por debajo.

## Requisitos del sistema

- PHP >= 8.2
- MySQL >= 8.0 (o MariaDB >= 10.6)
- Composer >= 2.x

## Instalación

composer install
cp .env.example .env
composer migrate up
composer seed

## Estructura del proyecto

```
php-redis/
├── bin/
│   ├── migrate.php
│   └── seed.php
├── public/
│   └── index.php
├── src/
│   ├── Config/
│   │   ├── container.php
│   │   └── middleware.php
│   ├── Controller/
│   │   └── AuthController.php
│   ├── Database/
│   │   ├── Migration/
│   │   │   └── 2026_08_16_000001_create_users_table.php
│   │   ├── Schema/
│   │   │   ├── Blueprint.php
│   │   │   └── SchemaBuilder.php
│   │   ├── Seed/
│   │   │   ├── Seeder.php
│   │   │   ├── UserSeeder.php
│   │   │   └── DatabaseSeeder.php
│   │   └── Migrator.php
│   ├── DTO/
│   │   └── LoginRequestDTO.php
│   ├── Exception/
│   │   ├── InvalidCredentialsException.php
│   │   ├── UserNotFoundException.php
│   │   └── ValidationException.php
│   ├── Factory/
│   │   └── LoggerFactory.php
│   ├── Middleware/
│   │   ├── AuthMiddleware.php
│   │   └── ErrorHandler.php
│   ├── Model/
│   │   └── User.php
│   ├── Repository/
│   │   ├── Contract/
│   │   │   └── UserRepositoryInterface.php
│   │   └── UserRepository.php
│   ├── Routes/
│   │   └── web.php
│   ├── Service/
│   │   ├── AuthService.php
│   │   └── JwtService.php
│   └── Validation/
│       └── LoginValidator.php
├── tests/
│   ├── Fake/
│   │   └── FakeUserRepository.php
│   ├── Integration/
│   └── Unit/
│       ├── LoginValidatorTest.php
│       └── Service/
│           └── AuthServiceTest.php
├── temp/
│   └── log/
├── .env
├── .env.example
├── composer.json
├── composer.lock
├── phpunit.xml
└── README.md
```

- **`public/`** — Lo único expuesto al navegador/servidor web. Contiene solo `index.php`, el punto de entrada. Todo lo demás (`src/`, `.env`, `vendor/`) queda fuera del alcance directo de una petición HTTP — es una medida de seguridad básica.
- **`src/`** — Todo el código fuente de la aplicación.
- **`tests/`** — Pruebas automatizadas, separadas del código de producción.
- **`bin/`** — Scripts ejecutables desde terminal (migraciones, seeders), distintos del código de la aplicación.
- **`temp/`** — Archivos generados en tiempo de ejecución (logs), no versionados en git.

## Dentro de `src/`

| Carpeta | Qué guarda | Por qué existe separada |
|---|---|---|
| **`Config/`** | `container.php` (definiciones del contenedor DI) y `middleware.php` (qué middlewares globales corren) | Es la configuración de *arranque* de la app — cómo se conecta todo, no lógica de negocio |
| **`Controller/`** | Clases que reciben `Request`/`Response` (HTTP) y coordinan la llamada a Service/Validator | Es la única capa que "sabe" de HTTP — traduce entre el mundo web y la lógica interna |
| **`Database/`** | Sistema de `Migration/` (esquema versionado), `Schema/` (Blueprint/SchemaBuilder) y `Seed/` (datos de prueba) | Versiona la estructura y el poblado de la BD igual que se versiona el código — reproducible en cualquier ambiente |
| **`DTO/`** | Objetos simples y tipados que representan los datos de entrada de un endpoint (`LoginRequestDTO`) | Evita pasar arrays sueltos (`$data['email']`) entre capas — da tipos, autocompletado, y un solo lugar donde se "parsea" el request |
| **`Exception/`** | Excepciones custom con nombre semántico (`InvalidCredentialsException`, `ValidationException`) | Comunican *qué* salió mal de forma explícita e imposible de ignorar, en vez de retornar `null` o valores ambiguos |
| **`Factory/`** | Clases cuya única responsabilidad es construir objetos complejos (`LoggerFactory`) | Separa el "cómo se arma algo" (lógica) de "qué se usa" (la definición en `container.php`) |
| **`Middleware/`** | Clases PSR-15 que interceptan la petición antes/después de la ruta (`AuthMiddleware`, `ErrorHandler`) | Lógica transversal que no pertenece a un Controller específico (auth, manejo de errores) |
| **`Model/`** | Representación de una entidad de datos (`User`) — propiedades, sin lógica de negocio | Es solo "la forma de los datos", reutilizable entre Repository, Service y Controller |
| **`Repository/`** | Clases que hablan con la base de datos y regresan Modelos (`UserRepository`) | Aísla el SQL/PDO del resto de la app — si cambia el motor de BD, solo se toca aquí |
| **`Repository/Contract/`** | Interfaces que definen el contrato de un repositorio (`UserRepositoryInterface`) | Permite que `Service` dependa del contrato, no de la implementación — se puede intercambiar MySQL, cache, o un fake de test sin tocar el Service |
| **`Routes/`** | Definición de endpoints (`web.php`) — qué URL llama a qué Controller | Mantiene el mapeo de rutas centralizado, como `routes/web.php` en Laravel |
| **`Service/`** | Lógica de negocio pura (`AuthService`, `JwtService`) — no sabe nada de HTTP ni de SQL directo | Es el corazón de la app: reglas de negocio reusables y testeables de forma aislada |
| **`Validation/`** | Reglas de validación de datos de entrada (`LoginValidator`) | Separa "¿los datos tienen la forma correcta?" de "¿la lógica de negocio es válida?" — son preguntas distintas |

## Dentro de `tests/`

| Carpeta | Qué guarda | Por qué |
|---|---|---|
| **`Unit/`** | Tests que prueban una clase aislada (ej. `AuthServiceTest`), usando fakes/mocks | Rápidos, no tocan BD ni red — prueban lógica pura |
| **`Integration/`** | Tests que prueban el flujo completo (HTTP → Controller → Service → BD real o de prueba) | Verifican que las piezas trabajen juntas correctamente, no solo aisladas |
| **`Fake/`** | Implementaciones falsas de las interfaces (`FakeUserRepository`) | Permite testear `Service` sin infraestructura real, gracias a que dependen de interfaces |

## Regla mental para saber dónde va algo nuevo

- ¿Habla de HTTP (Request/Response)? → `Controller/` o `Middleware/`
- ¿Es una regla de negocio? → `Service/`
- ¿Habla con la base de datos? → `Repository/`
- ¿Es solo la forma de un dato? → `Model/` (si viene de BD) o `DTO/` (si viene de un request)
- ¿Define un contrato que varias clases pueden cumplir? → `Contract/` dentro de la carpeta correspondiente
- ¿Construye un objeto complicado? → `Factory/`
- ¿Es configuración de arranque de la app? → `Config/`

## Validation vs. Exception — la diferencia clave

| | Validation | Exception (de dominio) |
|---|---|---|
| Pregunta que responde | "¿Los datos tienen la forma correcta?" | "¿La operación de negocio se pudo completar?" |
| Cuándo ocurre | **Antes** de tocar la lógica de negocio | **Durante** la ejecución de la lógica de negocio |
| Ejemplo | "el email está vacío", "el password mide 3 caracteres" | "el usuario no existe en la BD", "la contraseña no coincide" |
| Quién la dispara | Una capa dedicada solo a esto (`LoginValidator`) | Cualquier clase de negocio (`AuthService`, etc.) cuando algo no se puede completar |
| Analogía Laravel | `FormRequest` con `rules()` | `abort(404)` o `throw new ModelNotFoundException()` |

Una excepción interrumpe el flujo normal y obliga a que alguien más arriba decida qué hacer — a diferencia de un `return null`, que puede ignorarse por accidente. Guzzle sigue el mismo patrón: `ConnectException`, `ClientException`, `ServerException`, etc., para que el código que las atrapa pueda reaccionar distinto según qué salió mal exactamente.

---

## Sistema de migraciones

Sistema propio de migraciones inspirado en el de Laravel, construido sobre PDO. Permite describir cambios de esquema en PHP (en vez de SQL crudo), rastrear qué migraciones ya se ejecutaron, y revertirlas por lotes (*batches*).

### Estructura

```
src/Database/
├── Migration/
│   ├── 2026_08_16_000001_create_users_table.php
│   └── 2026_08_16_000002_create_posts_table.php
├── Schema/
│   ├── Blueprint.php       # describe columnas de una tabla
│   └── SchemaBuilder.php   # ejecuta el CREATE/DROP contra la BD
└── Migrator.php            # orquesta up/rollback/status
bin/
└── migrate.php              # CLI de entrada
```

### Formato de una migración

Cada archivo regresa una clase anónima con los métodos `up()` (aplicar el cambio) y `down()` (revertirlo):

```php
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
```

El nombre del archivo debe empezar con fecha (`YYYY_MM_DD_HHMMSS_descripcion`) para garantizar el orden de ejecución al ordenar alfabéticamente.

### Métodos disponibles en `Blueprint`

| Método | Genera |
|---|---|
| `id(string $name = 'id')` | `INT AUTO_INCREMENT PRIMARY KEY` |
| `string(string $name, int $length = 255)` | `VARCHAR(length) NOT NULL` |
| `text(string $name)` | `TEXT NOT NULL` |
| `integer(string $name)` | `INT NOT NULL` |
| `boolean(string $name)` | `TINYINT(1) NOT NULL DEFAULT 0` |
| `timestamps()` | columnas `created_at` / `updated_at` |
| `unique(string $column)` | índice `UNIQUE` sobre la columna |

### Cómo funciona el tracking (tabla `migrations`)

El sistema crea automáticamente una tabla `migrations` con:

- `migration` — nombre del archivo ya ejecutado
- `batch` — número de "tanda" en la que se aplicó
- `executed_at` — fecha de ejecución

Cada vez que corres `up`, todas las migraciones pendientes en esa corrida comparten el mismo número de `batch`. Esto permite revertir "la última tanda completa" con un solo comando, en el orden inverso al que se aplicaron.

### Comandos disponibles

> El script `migrate` está registrado en `composer.json` bajo `scripts`, y recibe el subcomando como argumento.

```bash
# Aplica todas las migraciones pendientes (nuevo batch)
composer migrate up

# Revierte el último batch completo
composer migrate rollback

# Revierte los últimos N batches
composer migrate rollback 3

# Revierte una migración puntual, sin importar su batch
composer migrate rollback:file 2026_08_16_000001_create_users_table

# Corre una migración específica, aunque existan otras pendientes antes
composer migrate run 2026_08_16_000002_create_posts_table

# Muestra el estado de todas las migraciones (aplicadas / pendientes)
composer migrate status
```

### Equivalencia con Laravel Artisan

| Este sistema | Artisan |
|---|---|
| `composer migrate up` | `php artisan migrate` |
| `composer migrate rollback` | `php artisan migrate:rollback` |
| `composer migrate rollback 3` | `php artisan migrate:rollback --step=3` |
| `composer migrate status` | `php artisan migrate:status` |

---

## Sistema de seeders

Sistema propio de seeders con Faker, inspirado en el de Laravel, para poblar la base de datos con datos de prueba.

### Requisitos

```bash
composer require --dev fakerphp/faker
```

### Estructura

```
src/Database/
├── Migration/
├── Schema/
├── Seed/
│   ├── Seeder.php              # clase base abstracta
│   ├── UserSeeder.php
│   └── DatabaseSeeder.php      # corre todos los seeders en orden
└── Migrator.php
bin/
├── migrate.php
└── seed.php                    # CLI de entrada
```

### Clase base `Seeder`

Todo seeder extiende de `App\Database\Seed\Seeder`, que ya inicializa `$this->faker` (instancia de `Faker\Generator`) y recibe la conexión `PDO`:

```php
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
        $this->faker = Factory::create();
    }

    abstract public function run(): void;
}
```

### Ejemplo de seeder

```php
<?php

declare(strict_types=1);

namespace Keel\Database\Seed;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $this->createUser('admin@test.com', 'password123', 'Admin');

        for ($i = 0; $i < 20; $i++) {
            $this->createUser(
                $this->faker->unique()->safeEmail(),
                'password123',
                $this->faker->name(),
            );
        }

        echo "UserSeeder: 21 usuarios creados.\n";
    }

    private function createUser(string $email, string $password, string $name): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO users (email, password, name) VALUES (?, ?, ?)'
        );
        $stmt->execute([$email, password_hash($password, PASSWORD_DEFAULT), $name]);
    }
}
```

### Métodos de Faker más usados

| Método | Genera |
|---|---|
| `$faker->name()` | Nombre completo |
| `$faker->firstName()` / `lastName()` | Nombre / apellido por separado |
| `$faker->safeEmail()` | Email con dominio reservado para pruebas (`@example.com`) |
| `$faker->unique()->...` | Garantiza que el valor no se repita en la misma ejecución |
| `$faker->phoneNumber()` | Teléfono según locale |
| `$faker->address()` / `city()` | Dirección / ciudad |
| `$faker->sentence()` / `paragraph()` | Texto random |
| `$faker->boolean()` | `true`/`false` random |
| `$faker->numberBetween(1, 100)` | Entero en un rango |
| `$faker->dateTimeBetween('-1 year', 'now')` | Fecha random en un rango |
| `$faker->randomElement([...])` | Un elemento random de un array |

El locale (idioma/región de los datos generados) se configura en `Factory::create('es_MX')` dentro de `Seeder.php`.

### `DatabaseSeeder` — orquestador

Corre los seeders en el orden definido (importante si hay dependencias entre tablas):

```php
<?php

declare(strict_types=1);

namespace Keel\Database\Seed;

use PDO;

class DatabaseSeeder
{
    public function __construct(private PDO $pdo) {}

    public function run(): void
    {
        $seeders = [
            UserSeeder::class,
            // PostSeeder::class,
        ];

        foreach ($seeders as $seederClass) {
            (new $seederClass($this->pdo))->run();
        }
    }
}
```

### Comandos disponibles

```bash
# Corre todos los seeders (vía DatabaseSeeder)
composer seed

# Corre un seeder específico
composer seed UserSeeder
```

### Evitar duplicados al re-sembrar

Para poder correr un seeder varias veces sin chocar con constraints (`UNIQUE`, etc.), truncar la tabla al inicio de `run()`:

```php
$this->pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
$this->pdo->exec('TRUNCATE TABLE users');
$this->pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
```

`FOREIGN_KEY_CHECKS = 0` es necesario si la tabla tiene relaciones por foreign key con otras tablas.

### Equivalencia con Laravel

| Este sistema | Laravel |
|---|---|
| `Seeder` (clase base) | `Illuminate\Database\Seeder` |
| `UserSeeder extends Seeder` | Igual |
| `DatabaseSeeder` | Igual, mismo nombre y propósito |
| `composer seed` | `php artisan db:seed` |
| `composer seed UserSeeder` | `php artisan db:seed --class=UserSeeder` |
| `$faker->name()` | Idéntico — misma librería (`fakerphp/faker`) por debajo |