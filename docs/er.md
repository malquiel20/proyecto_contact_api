# Modelo de datos de Contact API

```mermaid
erDiagram
    USERS ||--o{ CONTACTS : "tiene (user_id)"
    USERS ||--o{ PERSONAL_ACCESS_TOKENS : "inicia sesión (tokenable_id)"

    USERS {
        bigint id PK
        string url_avatar
        string name
        string phone_number UK
        string email UK
        string residence
        timestamp deleted_at
        timestamp email_verified_at
        string password
        string remember_token
    }

    CONTACTS {
        bigint id PK
        bigint user_id FK
        string name
        string number_phone "único por usuario"
        string email "único por usuario"
        string residence
        timestamp deleted_at
    }

    PERSONAL_ACCESS_TOKENS {
        bigint id PK
        bigint tokenable_id FK
        string name "device_name"
        string token "hash SHA-256"
    }
```

## Reglas

- Un usuario tiene muchos contactos; cada contacto pertenece a un solo usuario.
- `deleted_at` en ambas tablas: eliminar es un borrado lógico (soft delete).
- El correo y el teléfono de un usuario son únicos en toda la tabla `users`.
- El correo y el teléfono de un contacto no se pueden repetir dentro de la agenda
  del mismo usuario (se valida en la API; los contactos eliminados no cuentan).
- El nombre y la dirección (`residence`) sí se pueden repetir.
- Si un usuario se elimina definitivamente, sus contactos se eliminan en cascada.
