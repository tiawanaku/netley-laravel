# NET-010 — Estadísticas

## Estado
**Hecho, 2026-09-03 (Hito 4).** Los 11 reportes están implementados. Ver "Resultado" por las decisiones de alcance tomadas en cada uno.

## Objetivo
Página "Estadísticas" con los 11 reportes del sistema viejo: ranking de personal con más casos, clientes en mora, casos por estado y sucursal, pagos recibidos por mes/sucursal, saldo pendiente total y por cliente, casos por especialidad/delito, promedio de días para concluir casos, ranking de clientes, ingresos por abogado, casos próximos a vencer, clientes con más documentos. Corresponde a la Épica 9 de `VISION_Y_REQUERIMIENTOS_NETLEY_FILAMENT.md`.

## Épica origen
9 — Estadísticas (doc viejo).

## Entidades nuevas/afectadas
Ninguna tabla nueva — página custom con múltiples `TableWidget`/`ChartWidget` sobre [[Proceso]], [[Cliente Ejecutivo]], [[Finanza]]/[[PlanPago]], [[Personal]], [[Delito]], `ProcesoDocumento` existentes.

## Depende de
NET-004 (catálogo de Delito completo, para el reporte "casos por especialidad y delito") y comparte queries con NET-009.

## Complejidad
L — son 11 reportes distintos; considerar dividir en sub-tickets si se prioriza (p. ej. 10a: reportes de casos/personal, 10b: reportes financieros, 10c: reportes de documentos/mora).

## Notas
Cachear agregaciones pesadas (recomendación explícita del doc viejo, §8) — evaluar performance con volumen real antes de decidir la estrategia de cache. **No se implementó cache en este hito** (no había datos reales de volumen contra qué decidir la estrategia); queda para cuando haga falta.

## Resultado (implementado, 2026-09-03)
Página custom `App\Filament\Pages\Estadisticas` (Admin), sin filtros propios (a diferencia de NET-009, es un tablero de lectura rápida). Los 11 reportes del doc viejo, con las decisiones de alcance que hicieron falta para implementarlos sobre el esquema real de Netley (que no calca 1:1 el del sistema viejo):

1. Ranking de personal con más casos — directo, `Personal::withCount('procesos')`.
2. Clientes en mora — reutiliza `PlanPago::enMora()` de NET-009.
3. Casos por estado y sucursal (pivote) — "sucursal" = departamento del abogado (mismo proxy que NET-009).
4. Pagos por mes y sucursal — **acotado a los últimos 12 meses**, no todo el histórico, por legibilidad/rendimiento (decisión explícita, no un límite técnico).
5. Saldo pendiente total y por cliente — mismo cálculo que NET-009 (anticipo + cuotas pagadas), excluye clientes con saldo 0.
6. Casos por materia legal y delito — agrupa por `materia_legal_id` + `tipo_proceso`.
7. Promedio de días para concluir casos — **solo cuenta casos con `InformeCierre` (NET-008)**, calculado como `fecha_cierre − created_at` del Proceso. Los que se cerraron antes de NET-008 (o vía el wizard simple de `MiAgenda`, que no crea InformeCierre) no tienen una fecha de cierre confiable y quedan fuera — no se inventó una fecha sustituta.
8. Ranking de clientes con más casos / con más pagos — dos sub-reportes (8a/8b) en vez de uno combinado, para no forzar una sola tabla con columnas de naturaleza distinta.
9. Ingresos totales por abogado — sobre Recibos emitidos, agrupado por `Proceso.abogado_id`.
10. Casos próximos a vencer (≤15 días) — **el modelo `Proceso` no tiene un campo de fecha límite propio** (el doc viejo sí tenía `fecha_fin`, pero nunca se creó esa columna en Netley). Se implementó `Proceso::scopeProximosAVencer()`/`scopeVencidos()` derivando la fecha límite como `created_at + tiempo_proceso_meses` (nuevo accessor `Proceso::fecha_estimada_fin`). Si el negocio necesita poder corregir esa fecha a mano, hace falta agregar una columna dedicada — eso queda fuera de este ticket.
11. Clientes con más documentos subidos — cuenta `ProcesoDocumento` agrupado por `proceso.cliente_id`.

Tests: `tests/Feature/Net010EstadisticasTest.php` (scopes + los cálculos más propensos a error — mora, saldo, promedio de cierre, pivote — más un smoke test que renderiza la página completa con datos variados).

## Ver también
- [[Fusión Visión-Actual]]
- [[Dominios]]
- [[NET-009 - Panel de Control Financiero]]
