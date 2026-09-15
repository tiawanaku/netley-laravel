# NET-004 — Catálogos administrables

## Estado
**Implementado parcialmente** (2026-09-02) — materias legales, orígenes y departamentos migrados a tablas administrables. `cargos`/`profesiones` (enum `RolPersonal`) quedaron **fuera de alcance deliberadamente**, ver sección propia más abajo.

## Objetivo
Reemplazar los enums PHP hardcodeados (`CategoriaLegal`, `OrigenConsulta`) y las constantes duplicadas (`Personal::CIUDADES`/`Consulta::CIUDADES`) — más el catálogo simple de [[Delito]] con `area` como texto libre — por tablas administrables desde Filament, para que el equipo pueda mantenerlas sin tocar código. Corresponde a la Épica 12 de `VISION_Y_REQUERIMIENTOS_NETLEY_FILAMENT.md`.

## Épica origen
12 — Catálogos administrables (doc viejo).

## Lo implementado

- **3 tablas nuevas**: `materias_legales`, `origenes`, `departamentos` — cada una con Filament Resource propio (CRUD: nombre, slug, activo, orden) bajo el grupo de navegación "Catálogos" en el panel Admin.
- **`delitos.area`** (texto libre) → **`delitos.materia_legal_id`** (FK a `materias_legales`). `Delito::opcionesPara()` ahora filtra por ID en vez de string.
- **`consultas.tipo_proceso`** (string, cast `CategoriaLegal`) → **`consultas.materia_legal_id`** (FK). El nombre de columna se alineó con `procesos.materia_legal_id` — antes `Consulta.tipo_proceso` y `Proceso.tipo_proceso` significaban cosas distintas (materia legal vs. delito), lo cual era confuso.
- **`consultas.origen`** → **`consultas.origen_id`** (FK a `origenes`).
- **`consultas.ciudad`** / **`personal.ciudad`** → **`consultas.departamento_id`** / **`personal.departamento_id`** (FK a `departamentos` — son en realidad los 9 departamentos de Bolivia, no ciudades; se corrigió el label pero se mantuvo el nombre "Ciudad" visible donde ya era la convención de la UI).
- **`procesos.materia_legal`** (string, cast `CategoriaLegal`) → **`procesos.materia_legal_id`** (FK).
- Enums `CategoriaLegal` y `OrigenConsulta` **eliminados** (quedaron sin ningún uso tras la migración). Constantes `Personal::CIUDADES`/`Consulta::CIUDADES` eliminadas.
- Todos los `Select` afectados (Admin y Personal: `ProcesoResource`, `ConsultaResource` x2 paneles, `PersonalResource`, el wizard "Cierre" de `MiAgenda`, el trait `CreaClienteEjecutivoDirecto`) migrados de `->options(Enum::class)` a `->relationship(...)` (en Resources atados al modelo correspondiente) o a `->options(fn () => Modelo::query()->pluck(...))` explícito (en los dos wizards que no están atados al modelo `Proceso`/`Consulta` — `MiAgenda` no tiene Resource, y `CreaClienteEjecutivoDirecto` está atado a `Cliente`, no a `Proceso`; `->relationship('materiaLegal', ...)` ahí habría fallado).
- Seeders nuevos `MateriaLegalSeeder`, `OrigenSeeder`, `DepartamentoSeeder` (valores exactos de §4.2/§5 del doc viejo), y `DelitoSeeder` reescrito para resolver `materia_legal_id` vía slug. Factories (`ProcesoFactory`, `ConsultaFactory`, `PersonalFactory`) actualizadas con patrón "reusar catálogo existente o crearlo vía factory" para no depender de que los seeders hayan corrido (los tests usan `RefreshDatabase` sin seed automático).

### Bug resuelto como efecto directo (ya documentado en [[Backlog]])
`EspecialidadAbogado` (enum de [[Personal]], sin tocar) tenía 12 valores pero `CategoriaLegal` solo 7 — 3 sitios (`CreaClienteEjecutivoDirecto.php`, `MiAgenda.php`, `ProcesoResource.php`) comparaban `especialidad_abogado` contra `materia_legal` por string, así que un abogado con Constitucional/Ambiental/Minero/Migratorio/Propiedad Intelectual nunca podía recibir casos. `materias_legales` se sembró con los 12 valores (unión de ambos enums) y esas 3 comparaciones ahora resuelven el `slug` de la `MateriaLegal` seleccionada — verificado con una prueba dirigida en `tinker` y confirmado en vivo en el navegador (Admin → Procesos → Crear).

## Fuera de alcance — `cargos` / `profesiones` (RolPersonal)

Investigado con un agente de exploración dedicado antes de decidir el corte. `RolPersonal` tiene acoplamiento de comportamiento real, no solo de etiqueta:
- `Personal::esAbogado()` y 4 sitios con `where('rol', RolPersonal::Abogado)`.
- `MiAgenda.php:418` — `RolPersonal::from($data['especialidad'])`, lanza `ValueError` si el valor no es uno de los 7 casos del enum.
- `MiAgenda.php` hardcodea exactamente 2 roles (Psicólogo, Trabajador Social) como únicos destinos válidos de derivación.
- `PersonalResource` condiciona visibilidad/obligatoriedad de `especialidad_abogado` según `rol === RolPersonal::Abogado`.

Convertir esto a catálogo administrable de forma segura (sin romper ninguno de esos 7+ sitios) es trabajo suficiente para un ticket propio. `profesion` es un campo de texto libre sin acoplamiento encontrado, pero el sistema viejo la trata como catálogo hermano de Cargo — se agrupan y quedan pendientes juntos.

## Verificación realizada
- `php artisan migrate:fresh --seed` — sin errores, 12 materias / 6 orígenes / 9 departamentos / 461 delitos, cero FKs nulas.
- `php artisan test` — 40/40 en verde (incluye `Net003FiltrosCasosTest` corregido).
- Smoke test en navegador (Admin → Procesos → Crear): el select de Materia Legal muestra el catálogo completo, seleccionar "Civil" habilita y filtra correctamente el select de Delito (50 opciones), consistente con el catálogo real.

## Preguntas abiertas heredadas (sin resolver, no bloquean lo ya implementado)
- ¿Enum completo de `Estado` de Caso y de Consulta? (§10.4 del doc viejo)
- Catálogos de Delito para CIVIL/LABORAL/PENAL: el doc viejo no pudo relevarlos completos del sistema legado (carga AJAX dinámica) — los 461 delitos migrados vienen de `delitos.txt`, ya existente en este repo antes de NET-004, no del sistema viejo.

## Ver también
- [[Fusión Visión-Actual]]
- [[Dominios]]
