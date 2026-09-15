# NET-006 — Llamadas (Resource dedicado + unificación de vistas)

## Estado
**Hecho, 2026-09-03 (Hito 2).** Ver "Resultado" para lo que quedó dentro y fuera de alcance.

## Objetivo
Dar a las llamadas de seguimiento una vista propia (Diarias/Pendientes/Futuras/Cerradas), filtrable por materia, personal y departamento e imprimible en PDF. Corresponde a la Épica 8 de `VISION_Y_REQUERIMIENTOS_NETLEY_FILAMENT.md`.

## Épica origen
8 — Llamadas (doc viejo). Incluye también el gap menor de exportar Agenda a Excel (Épica 7) y exponer `AgendaResource` en el menú (ya señalado en [[Backlog]]).

## Entidades nuevas/afectadas
- **Decisión de diseño a tomar primero**: [[Agenda]] ya generaliza Cita/Llamada/Reunión con `tipo`, `estado`, `resultado` (solo para llamadas). Evaluar si conviene un `LlamadaResource` de Filament que simplemente filtre `Agenda::where('tipo', Llamada)` con tabs de estado (reutilizando el patrón de tabs de [[Proceso]]/[[Cliente Ejecutivo]] de NET-003), en vez de crear una entidad `Llamada` separada como tenía el sistema viejo.
- Si se opta por Resource dedicado: sin migración nueva, solo Filament Resource + tabs.

## Depende de
Nada directamente, pero conviene resolver junto con NET-004 si el filtro por "materia legal"/"delito" de la llamada pasa a usar los catálogos nuevos.

## Complejidad
S/M según la decisión de diseño de arriba.

## Preguntas abiertas heredadas
- El sistema viejo tenía **dos implementaciones distintas coexistiendo** del mismo módulo de Llamadas (§9.1 del doc viejo: variante de 2 vistas desde Dashboard vs. variante de 4 vistas desde el resto del panel). **No replicar esa duplicidad** — usar un único `LlamadaResource` (o filtro sobre Agenda) con tabs, como ya se hizo en NET-003 para Casos/Cliente Ejecutivo.

## Resultado (implementado, 2026-09-03)
Se optó por la opción ya prevista en este ticket: **sin entidad nueva**, `App\Filament\Resources\Llamadas\LlamadaResource` filtra `Agenda::where('tipo', Llamada)` (mismo patrón de NET-003), **solo panel Admin** por ahora (mismo razonamiento que NET-005: la exposición en el panel Personal queda pendiente de una decisión de matriz de permisos que este hito no cubrió).

- 4 nuevos scopes en `App\Models\Agenda` (reutilizables a futuro para Citas/Reuniones, no son exclusivos de Llamada): `deHoy()`, `atrasadas()`, `futuras()`, `cerradas()`. `atrasadas()`/`futuras()` cortan en el límite del día (`startOfDay()`/`endOfDay()`), no en el instante actual, para que una llamada de hoy cuya hora ya pasó siga contando como "de hoy" y no como "atrasada" — bug real encontrado y corregido durante el desarrollo de los tests (con el corte en `now()`, una llamada de hoy después de cierta hora aparecía en dos tabs a la vez).
- Tabs del `ListLlamadas`: Todas / Diarias / Pendientes / Futuras / Cerradas — 4 nombres calcan los del doc viejo (Diarias, Pendientes, Futuras, Cerradas/Todas), unificados en un solo Resource.
- Acciones de tabla: Reagendar, Cerrar (pide Resultado), Cancelar — mismo patrón que ya existía en `AgendaResource`.
- **Fuera de alcance, deferido explícitamente** (no se inventó): filtro por materia legal/delito (requeriría atravesar `consulta.materiaLegal`, la Llamada no tiene materia propia), filtro por departamento/sucursal, exportación a Excel de la agenda, e "imprimible en PDF" del listado — ninguno de estos existía como precedente en el código (los PDFs existentes son de un solo registro: recibo, informe, contrato) y no se fabricó una solución sin confirmar el alcance real.
- No se tocó `AgendaResource` (menú): se revisó el código y, a diferencia de lo que decía [[Backlog]], no se encontró ningún `shouldRegisterNavigation` ni mecanismo que lo oculte — parece ya visible en el menú del panel Admin. Esa nota del Backlog puede estar desactualizada; no se verificó en el navegador durante esta sesión.
- Tests: `tests/Feature/Net006LlamadasTest.php`.

## Ver también
- [[Fusión Visión-Actual]]
- [[Dominios]]
- [[NET-003 - Filtros casos pendientes y cerrados]]
