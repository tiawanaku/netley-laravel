# NET-008 — Cierre formal de Caso (InformeCierre) + Comisiones/Referidos

## Estado
**Hecho, 2026-09-03 (Hito 3).** Ver "Resultado".

## Objetivo
Estructurar el cierre de un Caso con un informe imprimible (estado, resultado, seguimiento posterior, pérdida en Bs. y quién la asume, informe final, fecha de cierre), en vez del cambio de estado simple que existe hoy vía el wizard "Cierre" de `MiAgenda`. Añadir además el registro de comisión y tipo de referido al crear un caso. Corresponde a la Épica 5 (HU-5.12) de `VISION_Y_REQUERIMIENTOS_NETLEY_FILAMENT.md`.

## Épica origen
5 — Cliente Ejecutivo (extensión), doc viejo.

## Entidades nuevas/afectadas
- Nuevo modelo `InformeCierre` (tabla `informe_cierres`): `caso_id` → [[Proceso]], `cierre_estado` (Concluido/Concluido pago pendiente/No concluido), `resultado` (Se ganó/Se perdió/Conciliación/No concluido), `seguimiento_post_cierre` (catálogo, ver §4.2 del doc viejo), `perdida_bs`, `perdida_asumida_por` (Personal o "NETLEY"), `informe_final`, `fecha_cierre`, `responsable`.
- Nuevos campos en [[Proceso]]: `comision_bs`, `comision_pct`, `tipo_referido` (Ninguno/Psicología/Trabajo Social/Otros).
- Reutiliza el patrón DomPDF ya existente (`app/Filament/Pages/Documentos.php`, `ConsultaInformeController`) para la vista imprimible/PDF del informe.
- El cierre actual del wizard `MiAgenda::cierreAction()` (portal Personal) queda como el disparador operativo del día a día; este ticket agrega la capa de informe formal encima, no reemplaza el flujo existente.

## Depende de
Nada bloqueante, pero conviene después de NET-004 si `seguimiento_post_cierre`/`tipo_referido` pasan a ser catálogos administrables en vez de enums.

## Complejidad
M.

## Preguntas abiertas heredadas
Ninguna específica del doc viejo más allá del enum completo de `Estado` de Caso (§10.4, compartida con NET-004).

## Resultado (implementado, 2026-09-03)
- Migración `informe_cierres` (`caso_id, cierre_estado, resultado, seguimiento_post_cierre, perdida_bs, perdida_asumida_por[netley|personal], perdida_asumida_por_personal_id, informe_final, fecha_cierre, responsable_id→User`) + columnas nuevas en `procesos` (`comision_bs, comision_pct, tipo_referido`).
- **Decisión de cardinalidad, distinta del doc viejo**: el doc viejo modela `Proceso` 0–1 `InformeCierre`; acá se implementó `hasMany` (`Proceso::informesCierre()`) + `Proceso::ultimoInformeCierre()` (el más reciente), porque el propio doc viejo describe una acción "Reactivar" que reabre un caso cerrado — con 0–1 se perdería el informe anterior en cada reapertura/recierre. Se prioriza no perder historial sobre calcar la cardinalidad original.
- Acción "Cerrar caso" en `ProcesoResource` (panel Admin, tabla de Procesos): formulario con los campos de arriba, crea el `InformeCierre` y cambia `Proceso.estado` a `Cerrado` en la misma acción. Visible solo si el Proceso no está ya Cerrado.
- Acción "Ver informe de cierre": visible solo si ya existe al menos un `InformeCierre`, abre `procesos.informe-cierre` en pestaña nueva — mismo patrón de ADR-003/004 (ruta HTTP dedicada, no modal) ya usado para el informe de Consulta.
- `InformeCierreController` (`show`/`pdf`) + vista `procesos/informe-cierre.blade.php`, calcada de `consultas/informe.blade.php` (mismo patrón `$paraPdf`, mismo layout).
- **No se tocó** el wizard `MiAgenda::cierreAction()` del panel Personal — sigue siendo el disparador operativo del día a día, tal como pedía el ticket.
- **Hallazgo no atribuible a este ticket, documentado y no arreglado en silencio**: la ruta nueva (y la ya existente de Consulta, mismo `Route::middleware('auth')`) responde 500 en vez de un 302 limpio cuando un usuario no autenticado la visita, porque el middleware `auth` por defecto redirige a una ruta nombrada `login` que no existe en esta app (cada panel Filament registra su propio login con otro nombre). Es una brecha preexistente compartida con `ConsultaInformeController`, no algo que introdujo NET-008 — arreglarla es una decisión sobre el guard/login por defecto que excede este ticket.
- Tests: `tests/Feature/Net008CierreFormalTest.php`.

## Ver también
- [[Fusión Visión-Actual]]
- [[Dominios]]
- [[Procesos]]
- [[Decisiones Arquitectónicas]] (ADR-003/004, patrón DomPDF + ruta dedicada reutilizado)
