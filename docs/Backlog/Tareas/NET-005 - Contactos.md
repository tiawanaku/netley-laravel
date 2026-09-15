# NET-005 — Contactos (directorio externo)

## Estado
**Hecho, 2026-09-03 (Hito 2).** Solo panel Admin — ver "Resultado".

## Objetivo
Registrar y buscar contactos externos (jueces, fiscales, instituciones) con institución, entidad, unidad, cargo, profesión y personal de Netley asignado. Corresponde a la Épica 3 de `VISION_Y_REQUERIMIENTOS_NETLEY_FILAMENT.md`.

## Épica origen
3 — Contactos (doc viejo).

## Entidades nuevas/afectadas
- Nuevo modelo `Contacto` (tabla `contactos`): `nombres`, `apellidos`, `cargo`, `profesion`, `institucion_ministerio`, `entidad_organo`, `unidad`, `telefono`, `correo`, `direccion`, `zona`, `ciudad`, `horario_contacto`, `nota`, `personal_netley_id` (FK a [[Personal]]).
- Alcance opcional: Curriculum de [[Personal]] (subida de PDFs) — gap menor de la Épica 2 que puede sumarse aquí en vez de abrir un ticket separado.

## Depende de
NET-004 (catálogos de cargo/profesión, si se decide reutilizarlos aquí también).

## Complejidad
S — CRUD simple, sin lógica de negocio compleja.

## Preguntas abiertas heredadas
- ¿"Instancia" del Caso (Juzgado/Fiscalía/Otro, ver NET-007) debe alimentarse del mismo catálogo/tabla que `Contacto`, o son conceptos independientes? (§10.8 del doc viejo) — **decidir antes de construir NET-007** para no duplicar el modelo de datos.

## Propuesta de resolución (borrador, a confirmar, 2026-09-03)
`Instancia` **no** reutiliza la tabla `Contacto` — son conceptos distintos (Contacto = persona externa; Instancia = ubicación procesal del caso). `Instancia` tendrá un `contacto_id` nullable opcional para vincular al juez/fiscal si aplica. Ver [[Roadmap de Implementación]] (Hito 2/3).

## Resultado (implementado, 2026-09-03)
- Migración `contactos`: `nombres, apellidos, cargo, profesion, institucion_ministerio, entidad_organo, unidad, telefono, correo, direccion, zona, departamento_id (FK nullable → departamentos, reemplaza el "ciudad" de texto libre del doc viejo por el catálogo de NET-004), horario_contacto, nota, personal_netley_id (FK nullable → personal)`.
- Modelo `App\Models\Contacto` + `ContactoFactory`.
- `App\Filament\Resources\Contactos\ContactoResource` — **solo panel Admin** por ahora. Se decidió no exponerlo también en el panel Personal en este hito: [[Fusión Visión-Actual]] §3 ya señalaba explícitamente que esa decisión "depende de la matriz de permisos de NET-014 — no se define sin esa decisión, para no inventar reglas de negocio", y el NET-014 implementado no llegó a cubrir Contactos (se quedó en VerCaso/Cliente Ejecutivo). Queda pendiente, no descartado.
- `cargo`/`profesion` se dejaron como texto libre (no se reutilizó `RolPersonal` ni un catálogo): describen personas externas (jueces, fiscales), un dominio distinto al `Cargo` interno de `Personal`.
- Tests: `tests/Feature/Net005ContactosTest.php`.

## Ver también
- [[Fusión Visión-Actual]]
- [[Roadmap de Implementación]]
- [[Dominios]]
