# NET-007 — Instancias y Seguimientos procesales del Caso

## Estado
**Hecho, 2026-09-03 (Hito 3).** Ver "Resultado".

## Objetivo
Gestionar la ubicación procesal de un Caso (Juzgado, Fiscalía u Otro) y agendar seguimientos sobre cada una, asignando personal, fecha, hora y detalle, con su propia agenda acotada al caso. Corresponde a la Épica 5 (HU-5.5, HU-5.6) de `VISION_Y_REQUERIMIENTOS_NETLEY_FILAMENT.md`.

## Épica origen
5 — Cliente Ejecutivo (extensión), doc viejo.

## Entidades nuevas/afectadas
- Nuevo modelo `Instancia` (tabla `instancias`): `caso_id` → [[Proceso]], `tipo` (Juzgado/Fiscalía/Otro), `datos` (según tipo), `contacto_id` nullable → `Contacto` (ver propuesta abajo).
- Nuevo modelo `Seguimiento` (tabla `seguimientos`): `caso_id`, `instancia_id`, `personal_id` → [[Personal]], `fecha`, `hora`, `detalle`.
- Se integran como `RelationManager` dentro de `ProcesoResource` (Admin) y en la página `VerCaso` (Personal), siguiendo el patrón ya usado para `ProcesoDocumento`/`DocumentoSolicitud`.

## Depende de
**Decidir primero** si `Instancia` reutiliza el mismo catálogo/tabla que [[Contacto]] (NET-005) — ver pregunta abierta heredada abajo y la propuesta de resolución. No empezar sin confirmar esa decisión para no construir dos modelos de datos redundantes.

## Complejidad
M — dos entidades nuevas + RelationManagers en dos paneles + agenda acotada al caso (`agenda_seguimientos.php` del viejo).

## Preguntas abiertas heredadas
- ¿"Instancia" (Juzgado/Fiscalía/Otro) debe alimentarse del mismo catálogo/tabla que `Contacto`, o son conceptos independientes? (§10.8 del doc viejo)

## Propuesta de resolución (borrador, a confirmar, 2026-09-03)
`Instancia` **no** reutiliza la tabla `Contacto` como fuente única — son conceptos distintos. Se agrega `contacto_id` nullable en `instancias` para vincular opcionalmente al juez/fiscal si ya está cargado como `Contacto`, sin forzarlo. Ver [[Roadmap de Implementación]] (Hito 2/3), donde se resuelve junto con NET-005.

## Resultado (implementado, 2026-09-03)
- Migraciones `instancias` (`caso_id, tipo[Juzgado/Fiscalía/Otro], datos, contacto_id nullable`) y `seguimientos` (`caso_id, instancia_id, personal_id nullable, fecha, hora, detalle`).
- Modelos `Instancia`/`Seguimiento` + relaciones en `Proceso` (`instancias()`, `seguimientos()`).
- Panel Admin: `InstanciasRelationManager` y `SeguimientosRelationManager` dentro de `ProcesoResource`. El selector de Instancia en `SeguimientosRelationManager` se limita a las instancias del propio Caso (no todas las instancias del sistema).
- Panel Personal: `VerCaso` gana dos acciones (`agregarInstanciaAction`, `agendarSeguimientoAction`) y una sección nueva en su vista con el listado de instancias/seguimientos. El Seguimiento agendado desde aquí queda con `personal_id` = quien lo agenda (mismo criterio que el resto del panel Personal).
- Tests: `tests/Feature/Net007InstanciasSeguimientosTest.php`.

## Ver también
- [[Fusión Visión-Actual]]
- [[Roadmap de Implementación]]
- [[Dominios]]
- [[Procesos]]
