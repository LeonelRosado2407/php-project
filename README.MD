# Keel

**Autor:** Leonel Rosado

Framework PHP construido desde cero sobre Slim4 — sin abstracciones de terceros
para el contenedor de dependencias, el sistema de migraciones, seeders o config.
Es un ejercicio de transparencia arquitectónica: entender qué hace Laravel (y
frameworks similares) por debajo, construyéndolo a mano.

📖 **La documentación completa vive en la [Wiki del proyecto](https://github.com/LeonelRosado2407/php-project/wiki)** — estructura de carpetas, sistema de migraciones, seeders y configuración. Este README es solo el punto de entrada rápido.

## Requisitos del sistema

- PHP >= 8.2
- MySQL >= 8.0 (o MariaDB >= 10.6)
- Composer >= 2.x

## Instalación

```bash
composer install
cp .env.example .env
composer migrate up
composer seed
```

## Comandos disponibles

```bash
composer serve          # servidor de desarrollo (localhost:8000)
composer migrate up      # aplica migraciones pendientes
composer migrate rollback # revierte el último batch
composer migrate status  # estado de migraciones
composer seed             # corre todos los seeders
composer make:migration   # genera un archivo de migración nuevo
composer test              # corre la suite de PHPUnit
composer stan               # corre PHPStan (análisis estático, nivel 5)
```

## Documentación

| Página | Contenido |
|---|---|
| [Estructura del proyecto](https://github.com/LeonelRosado2407/php-project/wiki/Estructura-del-proyecto) | Árbol de carpetas y por qué cada capa existe separada |
| [Sistema de migraciones](https://github.com/LeonelRosado2407/php-project/wiki/Sistema-de-migraciones) | Blueprint, batches, rollback, equivalencia con Artisan |
| [Sistema de seeders](https://github.com/LeonelRosado2407/php-project/wiki/Sistema-de-seeders) | Seeder base, Faker, DatabaseSeeder |
| [Sistema de configuración](https://github.com/LeonelRosado2407/php-project/wiki/Sistema-de-configuracion) | `env()`, `Settings`, `config.php` |

## CI

Cada push corre `composer install`, la suite de PHPUnit y PHPStan (ver `.github/workflows/ci.yml`).
