# NET-009 — Panel de Control Financiero

## Estado
**Hecho, 2026-09-03 (Hito 4).** Ver "Resultado".

## Objetivo
Página de reportes agregados filtrable por año, mes, sucursal y abogado: ingreso de nuevos clientes (1ra cuota), resumen global de ingresos con saldo pendiente, balance mensual por sucursal. Corresponde a la Épica 6 de `VISION_Y_REQUERIMIENTOS_NETLEY_FILAMENT.md`.

## Épica origen
6 — Pagos y Control Financiero (doc viejo). El registro de pagos en sí ([[Finanza]], [[PlanPago]], [[Recibo]]) ya está implementado y superado — este ticket es solo la capa de reportes agregados que falta.

## Entidades nuevas/afectadas
Ninguna tabla nueva — es una página custom de Filament (`Filament\Widgets\ChartWidget`/`TableWidget` + filtros) que consulta [[Finanza]]/[[PlanPago]]/[[Recibo]] existentes.

## Depende de
NET-004 si el filtro por "sucursal" pasa a ser el catálogo `departamentos` administrable en vez de un valor libre.

## Complejidad
M — sin entidades nuevas, pero varias queries agregadas a diseñar y probar contra datos reales.

## Notas
El doc viejo (§8) recomienda extraer las queries a `Services`/`Actions` reutilizables entre este panel y NET-010 (Estadísticas) y los widgets del Dashboard (Épica 1) — no duplicar la lógica de agregación en tres lugares.

## Resultado (implementado, 2026-09-03)
- Página custom `App\Filament\Pages\PanelFinanciero` (Admin), con formulario de filtros vivo (`año`, `mes`, `sucursal`, `abogado`) y 3 reportes: Ingreso Nuevos Clientes (1ra Cuota), Resumen de ingresos por Cliente Ejecutivo, Balance mensual por sucursal (sobre Recibos emitidos).
- **"Sucursal" no existe como campo propio** en `Proceso`/`Finanza`/`Recibo` — se usa el `departamento_id` del abogado asignado (ya existe desde NET-004) como proxy, documentado en el código en vez de agregar una columna nueva que ningún ticket pidió.
- Nuevos scopes reutilizables: `PlanPago::enMora()` / `PlanPago::porVencer()`. **Hallazgo importante**: `EstadoPago::Vencido` nunca lo asigna ningún job/comando (ya señalado en [[Backlog]]) — basarse en ese valor habría dejado el reporte de mora siempre vacío. Se calcula por fecha (`fecha < hoy` y no pagada), no por el enum.
- "Cancelado" en los reportes = `anticipo + suma de cuotas con estado Pagado` (no depende de si hay Recibo emitido o no, que es un paso administrativo posterior).
- Tests: `tests/Feature/Net009PanelFinancieroTest.php`.

## Ver también
- [[Fusión Visión-Actual]]
- [[Dominios]]
- [[NET-010 - Estadísticas]]
