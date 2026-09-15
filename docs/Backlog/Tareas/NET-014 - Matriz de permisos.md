# NET-014 — Matriz de permisos por Cargo/Nivel de acceso

## Estado
**Hecho (alcance actual), 2026-09-03.** Cubre todo lo que hoy existe en el panel Personal con una diferencia real de datos por rol. Queda **abierto/vivo**: cuando NET-005/006/009/010 agreguen Resources nuevos al panel Personal, deben engancharse al mismo patrón (ver "Resultado" abajo) en vez de quedar sin restringir.

## Objetivo
Definir y aplicar una matriz real de permisos por rol dentro del panel Personal (`/staff`), reemplazando el estado actual donde `Personal.estado = Activo` es la única restricción de acceso (no hay diferenciación por `rol` dentro del panel). Corresponde a la Épica 0 de `VISION_Y_REQUERIMIENTOS_NETLEY_FILAMENT.md`.

## Épica origen
0 — Autenticación y Control de Acceso (doc viejo).

## Estado actual verificado
`Personal::canAccessPanel()` (ver [[Arquitectura General]], ADR-001) solo exige `$panel->getId() === 'personal' && $this->estado === EstadoPersonal::Activo` — cualquier `Personal` activo, sin importar su `rol` (Administrador/Abogado/Asistente/Secretaria/Contador/Psicólogo/Trabajador Social), ve exactamente las mismas secciones del panel Personal hoy.

## Entidades nuevas/afectadas
- No requiere entidad nueva necesariamente: puede resolverse con `Filament\Policies` por Resource/Page usando el `rol` ya existente en [[Personal]], o con un paquete de roles/permisos (Filament Shield) si la matriz termina siendo compleja — **decidir el enfoque según la matriz real que dé el cliente**, no antes.
- Afecta la navegación objetivo de Personal descrita en [[Fusión Visión-Actual]] §3 — qué ve cada rol de Contactos, Llamadas, Estadísticas, etc. una vez construidos.

## Depende de
Nada técnicamente, pero **bloquea** la definición final de navegación de NET-005, NET-006, NET-009, NET-010 dentro del panel Personal.

## Complejidad
M — la implementación técnica es acotada; lo costoso es levantar la matriz real con el cliente (nunca se pudo verificar en el sistema viejo porque no había forma de generar sesión con un usuario de nivel bajo durante el levantamiento, §2 del doc viejo).

## Preguntas abiertas heredadas
- ¿Cuál es la matriz real de permisos por `Nivel de acceso` (1, 2, 3… ¿hasta qué número, y qué puede ver/editar cada uno)? (§10.1 del doc viejo) — **pregunta central de este ticket, sin respuesta todavía**.
- El doc viejo también deja sin confirmar si el nivel numérico es "1 = máximo acceso" o al revés.

## Propuesta de arranque (borrador inicial, superado — ver "Resultado")
Ante la falta de respuesta del cliente, se propuso al arrancar esta matriz de partida basada en `Personal.rol`:
- **Administrador**: acceso total dentro de `/staff` (igual que hoy).
- **Abogado**: Consultas, Cliente Ejecutivo/Casos (los suyos, `abogado_asignado`), Agenda propia, Llamadas.
- **Secretaria / Asistente**: Consultas, Agenda, Contactos, Llamadas — sin Finanzas ni Estadísticas.
- **Contador**: Finanzas/Pagos, sin Casos ni Consultas.
- **Psicologo / TrabajadorSocial**: solo sus propios casos derivados (referidos), Agenda propia.

Al inspeccionar el código para implementarla se encontró que aplicarla tal cual **habría roto funcionalidad ya en producción**: `ConsultaResource::getEloquentQuery()` ya filtra por asignación (`Agenda.responsable_id`), no por rol, y ese mismo mecanismo es el que hace funcionar el flujo de "Derivar a Psicólogo/Trabajador Social" del wizard de cierre (`MiAgenda::cierreAction()` → `cerrarDerivar()`): crea una nueva `Agenda` con `responsable_id` = el especialista derivado, quien la ve gracias a ese filtro por asignación. Restringir `ConsultaResource` por rol habría dejado a Psicólogo/Trabajador Social sin poder ver las consultas que se les derivan. Por eso el borrador de arriba **no se implementó como estaba planteado** — ver qué se hizo en su lugar.

## Resultado (implementado, 2026-09-03)
Alcance real: los únicos dos puntos del panel Personal donde hoy existe una diferencia de datos por rol son **VerCaso** (detalle de un Caso) y **ClienteResource** ("Cliente Ejecutivo"). `ConsultaResource` y `MiAgenda` se dejaron **deliberadamente sin restringir por rol** — ya están correctamente acotados por asignación, y así deben seguir.

- `Personal::esAdministrador()` y `Personal::puedeVerCaso(Proceso $proceso)` (nuevos métodos en el modelo, patrón ya usado por `esAbogado()`): Administrador ve cualquier Caso; Abogado solo el suyo (`Proceso.abogado_id`); el resto de roles no tiene hoy vía de datos hacia un Caso.
- `VerCaso::mount()` — antes tenía `abort_unless($proceso->abogado_id === $this->getPersonal()->id, 403)` **hardcodeado**, lo que dejaba fuera incluso al rol Administrador (bug real encontrado y corregido, no solo una mejora). Ahora usa `puedeVerCaso()`.
- `App\Filament\Personal\Resources\Clientes\ClienteResource` — se sobrescribieron `canViewAny()`, `canView()`, `canCreate()` y `getEloquentQuery()` directamente en el Resource (no se usó `Filament\Policies`: un Policy de Laravel sobre el modelo `Cliente` habría aplicado también al panel Admin, que comparte el mismo modelo, y el alcance de este ticket es solo `/staff`). Administrador ve/crea todo; Abogado ve solo clientes con algún Proceso donde `abogado_id` sea el suyo; el resto de roles no ve la sección (ítem de menú oculto, ruta bloqueada con 403).
- Tests: `tests/Feature/Net014MatrizPermisosTest.php` (35 assertions) cubre `puedeVerCaso()`, acceso a `VerCaso` por rol, y el scoping de `ClienteResource`. Se ajustó `tests/Feature/Net003FiltrosCasosTest.php` (fijar `rol: Administrador` en el actor Personal existente) para que el scoping nuevo no interfiera con lo que ese test ya verificaba.

**Patrón para NET-005/006/009/010**: cuando esos tickets agreguen Resources nuevos al panel Personal, decidir caso por caso si el dato ya viene acotado por asignación (como Consulta/Agenda — no tocar) o si necesita scoping por rol (como Cliente Ejecutivo — replicar el mismo patrón de overrides directos en el Resource, no Policies de Laravel, para no afectar al panel Admin).

## Ver también
- [[Fusión Visión-Actual]]
- [[Roadmap de Implementación]]
- [[Arquitectura General]] (ADR-001)
- [[Dominios]]
