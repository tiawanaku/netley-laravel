# Credenciales de Prueba (entorno local)

> Generadas por los seeders del proyecto (`dev/database/seeders`). Son credenciales de **desarrollo/local únicamente** — no usar en producción ni reutilizar estas contraseñas fuera de este entorno.

Para (re)generarlas: desde `dev/`, con la base de datos accesible:

```bash
php artisan migrate:fresh --seed
```

o, si la base ya está migrada y solo quieres asegurar que existan:

```bash
php artisan db:seed
```

## Panel Admin — `http://127.0.0.1:8000/admin/login`

Guard `web`, modelo `User`. Login por **email**.

| Campo | Valor |
|---|---|
| Email | `admin@netley.test` |
| Password | `password` |
| Nombre | Admin Netley |

Fuente: `database/seeders/DatabaseSeeder.php`.

## Panel Staff (Personal) — `http://127.0.0.1:8000/staff/login`

Guard `personal`, modelo `Personal`. Login por **usuario** (no email).

| Campo | Valor |
|---|---|
| Usuario | `70000000` |
| Password | `password` |
| Nombre | Abogado De Prueba |
| CI | 0000000 |
| Email | staff@netley.test |
| Rol | Administrador |
| Cargo | Representante legal |
| Profesión / Especialidad | Abogado / Civil |
| Estado | Habilitado |
| `must_change_password` | `false` (no pedirá cambio de contraseña al entrar) |

Fuente: `database/seeders/PersonalSeeder.php`.

## Panel Portal (Cliente) — `http://127.0.0.1:8000/portal/login`

Guard `clientes`, modelo `Cliente`. Login por **usuario** (no email).

**No hay seeder para `Cliente`** — no existe ningún cliente de prueba precargado por defecto. Para probar este panel hay que crear uno manualmente desde el panel Admin, de dos formas:

1. **Alta directa**: Admin → Clientes → Nuevo (crea Cliente + Proceso + Finanza en una sola operación).
2. **Conversión de una Consulta**: Admin → Consultas → agendar cita → dar respuesta → "Convertir a cliente".

En ambos casos el sistema autogenera `usuario` (a partir del teléfono) y una contraseña temporal, que el controlador muestra **una sola vez** en un mensaje flash tras la creación — hay que capturarla en ese momento porque no queda visible después (no hay endpoint de "olvidé mi contraseña" para este guard).

### Cliente de prueba ya creado (16/09/2026)

Se creó vía "Alta directa" para poder probar el portal sin pasos manuales. Credenciales verificadas — inicio de sesión probado en el navegador:

| Campo | Valor |
|---|---|
| Usuario | `65432100` |
| Password | `9Jd\|So_t_g` |
| Nombre | Usuario De Prueba |
| Teléfono | 65432100 |
| Caso | Divorcio de mutuo acuerdo (Civil) |
| Abogado asignado | Laura Rojas Mamani |
| Costo del caso | Bs. 3,000.00 |

Este cliente ya tiene una etapa de proceso registrada ("Trámite inicial") para que se vea el flujo completo de principio a fin: el abogado la informó desde el panel Staff (`staff/casos/3`) y se refleja tanto en Admin (`admin/casos/3`) como en este portal.

## Notas

- Los tres guards son independientes (`web`, `personal`, `clientes`) sobre la misma base de datos — ver `docs/Estado Actual del Sistema.md` §3.
- El staff creado desde el panel Admin (`PersonalController@store`) también genera credenciales de portal-staff vía `Personal::generarAccesoPortal()`, mostradas una única vez igual que en el caso de `Cliente`.
- Si necesitas resetear el acceso de un `Personal` existente sin recordar su contraseña: Admin → Personal → botón "Resetear acceso" (`POST admin/personal/{id}/resetear-acceso`).
