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

## Modelo de datos

Diagrama entidad-relación: [docs/er.md](docs/er.md)

## Datos de prueba

```bash
php artisan migrate:fresh --seed
```

Crea un administrador (admin@contact.test / password) con 10 contactos y
3 usuarios más con 5 contactos cada uno. Solo para desarrollo local.

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

| Método    | Ruta                    | Descripción                          | Auth   |
|-----------|-------------------------|--------------------------------------|--------|
| GET       | /api/v1/health          | Estado del servicio                  | No     |
| POST      | /api/v1/auth/login      | Inicia sesión y entrega un token     | No     |
| POST      | /api/v1/auth/logout     | Cierra sesión y revoca el token      | Bearer |
| GET       | /api/v1/profile         | Perfil del usuario autenticado       | Bearer |
| PUT/PATCH | /api/v1/profile         | Actualiza el perfil del usuario      | Bearer |
| GET       | /api/v1/contacts        | Lista mis contactos (15 por página)  | Bearer |
| POST      | /api/v1/contacts        | Agrega un contacto                   | Bearer |
| GET       | /api/v1/contacts/{id}   | Perfil del contacto                  | Bearer |
| PUT/PATCH | /api/v1/contacts/{id}   | Actualiza un contacto                | Bearer |
| DELETE    | /api/v1/contacts/{id}   | Elimina un contacto (borrado lógico) | Bearer |

## Reglas de la API

| Situación                                         | Respuesta |
|---------------------------------------------------|-----------|
| Datos inválidos                                   | 422 con `errors` campo por campo |
| Link que no existe (ruta o contacto)              | 404 `El recurso solicitado no existe.` |
| Sin iniciar sesión                                | 401 con `login_url` |
| Contacto que existe pero es de otro usuario       | 403 `No tienes permiso para acceder a este contacto.` |
| Correo o teléfono de usuario ya en uso            | 422 (únicos en toda la tabla users) |
| Correo o teléfono repetido en mis contactos       | 422 (únicos dentro de la agenda de cada usuario) |
| Nombre y dirección                                | Se pueden repetir |

## Autenticación

1. POST /api/v1/auth/login con email, password y device_name.
2. Guardar el token de la respuesta.
3. Enviar en cada petición protegida:
   `Authorization: Bearer {token}` y `Accept: application/json`

Sin token (o con un token revocado) la API responde 401 con la ruta del login:

```json
{
  "message": "Debes iniciar sesión para acceder a este recurso.",
  "login_url": "http://127.0.0.1:8000/api/v1/auth/login"
}
```

El login admite 5 intentos por minuto por correo e IP (luego responde 429).

## Flujo de trabajo

Git Flow (main, develop, feature/\*, release/\*, hotfix/\*),
Conventional Commits y versionado semántico.
Los cambios se registran en [CHANGELOG.md](CHANGELOG.md).

## Autor

malquiel20 · github.com/malquiel20

## Licencia

Distribuido bajo la licencia MIT. Ver [LICENSE.md](LICENSE.md).
