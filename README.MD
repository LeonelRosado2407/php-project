# Author
Leonel Rosado
## Objetivo
Proyecto para reforzar conocimientos de php y cómo se hace un proyecto serio; para ello esto es un REST API.

## Estructura de proyecto
```
php-redis
├─ .phpunit.result.cache
├─ composer.json
├─ composer.lock
├─ phpunit.xml
├─ public
│  └─ index.php
├─ README.md
├─ src
│  ├─ Config
│  │  ├─ container.php
│  │  └─ middleware.php
│  ├─ Controller
│  │  └─ AuthController.php
│  ├─ Database
│  │  ├─ Migration
│  │  │  ├─ Down
│  │  │  │  └─ 1-0-0.sql
│  │  │  └─ Up
│  │  │     └─ 1-0-0.sql
│  │  ├─ Script
│  │  └─ Seed
│  │     └─ add_fake_info.sql
│  ├─ DTO
│  │  └─ LoginRequestDTO.php
│  ├─ Exception
│  │  ├─ InvalidCredentialsException.php
│  │  ├─ UserNotFoundException.php
│  │  └─ ValidationException.php
│  ├─ Factory
│  │  └─ LoggerFactory.php
│  ├─ Middleware
│  │  ├─ AuthMiddleware.php
│  │  └─ ErrorHandler.php
│  ├─ Model
│  │  └─ User.php
│  ├─ Repository
│  │  ├─ Contract
│  │  │  └─ UserRepositoryInterface.php
│  │  └─ UserRepository.php
│  ├─ Routes
│  │  └─ web.php
│  ├─ Service
│  │  ├─ AuthService.php
│  │  └─ JwtService.php
│  └─ Validation
│     └─ LoginValidator.php
└─ tests
   ├─ Fake
   │  └─ FakeUserRepository.php
   ├─ Integration
   └─ Unit
      ├─ LoginValidatorTest.php
      └─ Service
         └─ AuthServiceTest.php

```

# Estructura del proyecto — php-redis (Slim4)

## Estructura general

```
php-redis/
├── public/
├── src/
├── tests/
└── temp/
```

- **`public/`** — Lo único expuesto al navegador/servidor web. Contiene solo `index.php`, el punto de entrada. Todo lo demás (`src/`, `.env`, `vendor/`) queda fuera del alcance directo de una petición HTTP — es una medida de seguridad básica.
- **`src/`** — Todo el código fuente de la aplicación.
- **`tests/`** — Pruebas automatizadas, separadas del código de producción.
- **`temp/`** — Archivos generados en tiempo de ejecución (logs), no versionados en git.

## Dentro de `src/`

| Carpeta | Qué guarda | Por qué existe separada |
|---|---|---|
| **`Config/`** | `container.php` (definiciones del contenedor DI) y `middleware.php` (qué middlewares globales corren) | Es la configuración de *arranque* de la app — cómo se conecta todo, no lógica de negocio |
| **`Controller/`** | Clases que reciben `Request`/`Response` (HTTP) y coordinan la llamada a Service/Validator | Es la única capa que "sabe" de HTTP — traduce entre el mundo web y la lógica interna |
| **`DTO/`** | Objetos simples y tipados que representan los datos de entrada de un endpoint (`LoginRequestDTO`) | Evita pasar arrays sueltos (`$data['email']`) entre capas — da tipos, autocompletado, y un solo lugar donde se "parsea" el request |
| **`Exception/`** | Excepciones custom con nombre semántico (`InvalidCredentialsException`, `ValidationException`) | Comunican *qué* salió mal de forma explícita e imposible de ignorar, en vez de retornar `null` o valores ambiguos |
| **`Factory/`** | Clases cuya única responsabilidad es construir objetos complejos (`LoggerFactory`) | Separa el "cómo se arma algo" (lógica) de "qué se usa" (la definición en `container.php`) |
| **`Middleware/`** | Clases PSR-15 que interceptan la petición antes/después de la ruta (`AuthMiddleware`, `ErrorHandler`) | Lógica transversal que no pertenece a un Controller específico (auth, manejo de errores, CORS) |
| **`Model/`** | Representación de una entidad de datos (`User`) — propiedades, sin lógica de negocio | Es solo "la forma de los datos", reutilizable entre Repository, Service y Controller |
| **`Repository/`** | Clases que hablan con la base de datos y regresan Modelos (`UserRepository`) | Aísla el SQL/PDO del resto de la app — si cambias de motor de BD, solo se toca aquí |
| **`Repository/Contract/`** | Interfaces que definen el contrato de un repositorio (`UserRepositoryInterface`) | Permite que `Service` dependa del contrato, no de la implementación — se puede intercambiar MySQL, cache, o un fake de test sin tocar el Service |
| **`Routes/`** | Definición de endpoints (`web.php`) — qué URL llama a qué Controller | Mantiene el mapeo de rutas centralizado, como `routes/web.php` en Laravel |
| **`Service/`** | Lógica de negocio pura (`AuthService`, `JwtService`) — no sabe nada de HTTP ni de SQL directo | Es el corazón de la app: reglas de negocio reusables y testeables de forma aislada |
| **`Validation/`** | Reglas de validación de datos de entrada (`LoginValidator`) | Separa "¿los datos tienen la forma correcta?" de "¿la lógica de negocio es válida?" — son preguntas distintas |
| **`Database/`** | `Migration/` (cambios de esquema versionados), `Seed/` (datos de prueba), `Script/` (utilidades sueltas) | Versiona la estructura de la BD igual que se versiona el código — reproducible en cualquier ambiente |

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