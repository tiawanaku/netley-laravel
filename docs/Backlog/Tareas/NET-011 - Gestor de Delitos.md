# NET-011 — Gestor de Delitos

## Estado
**Hecho (cola + asignación manual), 2026-09-03 (Hito 5).** Auto-clasificación deliberadamente fuera de alcance — ver "Resultado".

## Objetivo
Cola de respuestas de consulta ([[Answer]]) sin `delito_denuncia_id` clasificado, con asignación manual y un botón de "auto-clasificación" que sugiera el delito a partir del texto. Corresponde a la Épica 11 de `VISION_Y_REQUERIMIENTOS_NETLEY_FILAMENT.md` — **la única automatización tipo IA detectada en el sistema viejo**.

## Épica origen
11 — Gestor de Delitos (doc viejo).

## Entidades nuevas/afectadas
- Página custom Filament con lista de "excepciones" (`Answer` sin delito clasificado) + formulario inline de asignación.
- Depende de que [[Delito]] tenga catálogo completo por materia (NET-004) para que la clasificación manual tenga opciones reales que elegir.

## Depende de
NET-004 (catálogo de Delito completo).

## Complejidad
S para la cola + asignación manual. La auto-clasificación es de complejidad y alcance **no definidos** — ver nota abajo.

## Preguntas abiertas heredadas
- El doc viejo marca explícitamente que la decisión de mantener o mejorar la auto-clasificación (heurística de keywords vs. embeddings/LLM) **es una decisión de producto pendiente de confirmar con el cliente** (§Épica 11, nota técnica). No asumir alcance de IA sin esa confirmación — construir primero la cola + asignación manual, dejar la auto-clasificación como fase 2 explícita.

## Resultado (implementado, 2026-09-03)
- **Hallazgo previo a implementar**: `Answer` (el `Respuesta` real de Netley) no tenía ningún campo de clasificación — a diferencia del `Respuesta.delito_denuncia_id` del doc viejo, que este ticket asumía que ya existía. Se agregó la columna `answers.delito` (migración `2026_09_03_150000_add_delito_to_answers_table`).
- **Decisión de diseño**: `delito` es texto libre, **no** una FK a `delitos` — se siguió el mismo patrón ya establecido en `Proceso.tipo_proceso` (`Delito::opcionesPara()` funciona como catálogo de sugerencias sobre un campo de texto, no como relación estricta), en vez de introducir un enfoque nuevo (FK) que ningún otro punto del código usa.
- `Answer::scopeSinClasificar()` — `delito` nulo o vacío.
- `App\Filament\Pages\GestorDelitos` (Admin): tabla de `Answer::sinClasificar()` con contador de pendientes y acción "Clasificar" (Select filtrado por la materia legal de la Consulta de origen, con opción de crear un Delito nuevo — mismo patrón de `ProcesoResource`).
- **No implementado, a propósito**: la auto-clasificación (heurística o IA). Es la única pieza de este ticket que el propio doc viejo marca como decisión de producto pendiente — no se fabricó un alcance sin esa confirmación.
- Tests: `tests/Feature/Net011GestorDelitosTest.php`.

## Ver también
- [[Fusión Visión-Actual]]
- [[Dominios]]
