# NET-013 — Comentarios (moderación de testimonios)

## Estado
**Hecho, 2026-09-03 (Hito 6).** Ver "Resultado".

## Objetivo
Moderar testimonios enviados desde el sitio público (aprobar/rechazar), con contadores por estado, mostrando en el sitio público solo los aprobados. Corresponde a la Épica 10 de `VISION_Y_REQUERIMIENTOS_NETLEY_FILAMENT.md`.

## Épica origen
10 — Comentarios (doc viejo).

## Entidades nuevas/afectadas
- Nuevo modelo `Comentario` (tabla `comentarios`): `nombre`, `texto`, `estado` (Pendiente/Aprobado/Rechazado).
- `ComentarioResource` (Admin) con acciones de tabla `Aprobar`/`Rechazar`.
- Posible relación con [[Answer]] si se decide que una respuesta marcada `publicar = Sí` (campo del sistema viejo que hoy no existe en `Answer`, ver [[Fusión Visión-Actual]] §1) alimenta este módulo en vez de un formulario público independiente — **a definir junto con NET-012**.

## Depende de
NET-012 (sitio público — es donde se muestran los testimonios aprobados y de donde llegan los nuevos).

## Complejidad
S.

## Resultado (implementado, 2026-09-03)
- Modelo `Comentario` (`nombre, texto, estado[Pendiente/Aprobado/Rechazado]`) — **decisión sobre la "posible relación con Answer"**: se implementó como entidad independiente alimentada por un formulario público (no vinculada a `Answer.publicar`), tal como describe el propio campo de este ticket y el doc viejo (Comentario se llena desde visitantes del sitio, no desde staff marcando una respuesta interna como publicable).
- `ComentarioResource` (Admin): tabs Todos/Pendientes/Aprobados/Rechazados con contador en cada uno (pedido explícito del ticket), acciones Aprobar/Rechazar en la tabla. Sin pantalla de creación manual — los Comentarios solo llegan del formulario público.
- Formulario público de testimonios embebido en la home de NET-012, solo los `Aprobado` se muestran ahí.
- Tests: `tests/Feature/Net013ComentariosTest.php`.

## Ver también
- [[Fusión Visión-Actual]]
- [[Dominios]]
- [[NET-012 - Sitio público]]
