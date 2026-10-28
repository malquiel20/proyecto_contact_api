# Contact API

[![tests](https://github.com/malquiel20/proyecto_contact_api/actions/workflows/tests.yml/badge.svg)](https://github.com/malquiel20/proyecto_contact_api/actions/workflows/tests.yml)
![PHP](https://img.shields.io/badge/PHP-8.3-777BB4)
![Laravel](https://img.shields.io/badge/Laravel-13-FF2D20)
![Licencia](https://img.shields.io/badge/licencia-MIT-blue)

API REST para gestionar una agenda de contactos personal.

- Versión actual: v0.1.0

## Contenido

- [Tecnologías](#tecnologías)
- [Instalación](#instalación)
- [Pruebas](#pruebas)
- [Endpoints](#endpoints)
- [Flujo de trabajo](#flujo-de-trabajo)
- [Licencia](#licencia)

## Tecnologías

PHP 8.3, Laravel 13, MySQL 8, Laravel Sanctum, Pest, Scramble.

## Requisitos

PHP 8.3+, Composer 2, MySQL 8, Git.

## Instalación

```bash
git clone https://github.com/malquiel20/proyecto_contact_api.git
cd proyecto_contact_api
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

Antes de migrar, creen las bases de datos en MySQL:

```sql
CREATE DATABASE db_contact_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE db_contact_api_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## Variables de entorno

| Variable    | Ejemplo        | Descripción           |
|-------------|----------------|-----------------------|
| DB_DATABASE | db_contact_api | Base de datos MySQL   |
| DB_PREFIX   | a07_           | Prefijo de las tablas |

## Pruebas

```bash
php artisan test
```

Las pruebas usan la base `db_contact_api_test` (ver `phpunit.xml`).

## Documentación de la API

Con el servidor local: http://127.0.0.1:8000/docs/api

La colección de Insomnia está en [docs/insomnia](docs/insomnia).

## Endpoints

| Método | Ruta           | Descripción         | Auth |
|--------|----------------|---------------------|------|
| GET    | /api/v1/health | Estado del servicio | No   |

## Flujo de trabajo

Git Flow (main, develop, feature/\*, release/\*, hotfix/\*),
Conventional Commits y versionado semántico.
Los cambios se registran en [CHANGELOG.md](CHANGELOG.md).

## Autor

malquiel20 · github.com/malquiel20

## Licencia

Distribuido bajo la licencia MIT. Ver [LICENSE.md](LICENSE.md).
