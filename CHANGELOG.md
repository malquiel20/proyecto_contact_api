# Changelog

Todos los cambios importantes de este proyecto se registran en este archivo.

El formato sigue [Keep a Changelog](https://keepachangelog.com/es-ES/1.1.0/)
y el proyecto usa [versionado semántico](https://semver.org/lang/es/).

## [Unreleased]

### Added

- GET /api/v1/health
- Errores en JSON para rutas /api (404 y 405 con mensajes en español)
- Documentación OpenAPI con Scramble en /docs/api
- Campos url_avatar, phone_number (único), residence y deleted_at en usuarios
- Tabla contacts con llave foránea a users y borrado lógico
- Columnas email y residence en contactos
- Modelo Contact y relación User → contacts
- Factories y seeder con datos de ejemplo
- Diagrama entidad-relación en docs/er.md
- Login con token de Sanctum (POST /api/v1/auth/login)
- Cierre de sesión que revoca el token (POST /api/v1/auth/logout)
- Respuesta 401 en JSON con la ruta del login para quien no ha iniciado sesión
- Límite de 5 intentos de login por minuto
- Esquema Bearer en la documentación
- Perfil de usuario: GET y PUT/PATCH /api/v1/profile
- Correo y teléfono del usuario únicos; nombre y dirección se pueden repetir
- CRUD de contactos: listar, agregar, ver perfil, actualizar y eliminar (soft delete)
- Correo y teléfono de contacto únicos dentro de la agenda de cada usuario
- Política: un usuario no puede ver, editar ni eliminar contactos de otro (403)
