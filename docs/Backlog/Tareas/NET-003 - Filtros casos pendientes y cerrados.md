# NET-003 — Filtros de casos pendientes y cerrados

## Estado

Implementado (pendiente de commit/despliegue).

## Regla de negocio definitiva

**Caso/Proceso** (`App\Models\Proceso`, enum `App\Enums\EstadoProceso`, sin
cambios en los estados existentes):

- **Pendiente:** `estado = Activo`.
- **Cerrado:** `estado != Activo` (es decir, `Cerrado` o `Archivado`). El
  estado `Archivado` no se usa en ningún flujo real del sistema hoy, pero se
  incluyó en "Cerrado" porque semánticamente representa un caso que ya no
  está en curso.

**Cliente Ejecutivo** (`App\Models\Cliente`), en función de sus Procesos
relacionados (`Cliente::procesos()`):

- **Pendiente:** tiene al menos un Proceso Activo.
- **Cerrado:** no tiene ningún Proceso Activo.
- **Cliente Ejecutivo sin ningún Proceso:** se clasifica como **Cerrado**.
  Decisión de negocio tomada explícitamente para esta implementación: la
  regla "todos sus casos están cerrados" se cumple de forma vacía cuando no
  hay casos, y se prefirió esta interpretación porque permite expresar la
  condición con una única consulta `whereDoesntHave` (sin casos pendientes),
  reutilizable y eficiente, en vez de introducir una tercera categoría
  ("sin casos") no contemplada en los criterios de aceptación de NET-003.

## Solución técnica

La lógica de dominio se centralizó en scopes Eloquent, para que Admin y
Personal consulten exactamente la misma fuente:

- `Proceso::scopePendientes()` / `Proceso::scopeCerrados()` — filtran por
  `estado` directamente.
- `Proceso::estaCerrado` (accessor) — equivalente a nivel de instancia.
- `Cliente::scopeConCasosPendientes()` — `whereHas('procesos', pendientes)`.
- `Cliente::scopeConCasosCerrados()` — `whereDoesntHave('procesos', pendientes)`.

El filtro se implementó como **tabs/botones** (Todos / Pendientes / Cerrados)
en la parte superior de cada tabla, usando el sistema nativo de tabs de
Filament (`ListRecords::getTabs()`, `Filament\Schemas\Components\Tabs\Tab`),
en vez de un `SelectFilter` en el panel de filtros — por pedido explícito de
UX. Cada `Tab::modifyQueryUsing()` en los 3 Resources solo invoca los scopes
de dominio; no hay lógica de negocio duplicada en las capas de UI. En el
panel Personal, la sección "Mis casos" de `MiAgenda` (vista Blade simple,
sin `ProcesoResource` — ver Hallazgo adicional) reutiliza los mismos scopes
de `Proceso` a través de la propiedad Livewire `$filtroCasos`, con un control
de botones equivalente (`x-filament::button.group`) para mantener la misma
experiencia "botones arriba de la tabla" en los 4 lugares.

El filtro "Estado" ya existente en `ProcesoResource` (que expone los 3
valores exactos del enum vía `SelectFilter`, en el panel de filtros) se
mantuvo sin cambios; los nuevos tabs Pendientes/Cerrados son un control
adicional, no un reemplazo.

## Archivos modificados

- `app/Models/Proceso.php` — scopes `pendientes()`/`cerrados()`, accessor
  `estaCerrado`.
- `app/Models/Cliente.php` — scopes `conCasosPendientes()`/`conCasosCerrados()`.
- `app/Filament/Resources/Procesos/Pages/ListProcesos.php` — tabs
  Todos/Pendientes/Cerrados (Admin, Casos).
- `app/Filament/Resources/Clientes/Pages/ListClientes.php` — tabs
  Todos/Pendientes/Cerrados (Admin, Cliente Ejecutivo).
- `app/Filament/Personal/Resources/Clientes/Pages/ListClientes.php` — tabs
  Todos/Pendientes/Cerrados (Personal, Cliente Ejecutivo).
- `app/Filament/Personal/Pages/MiAgenda.php` — propiedad `$filtroCasos` +
  método `setFiltroCasos()`, aplicados en `getCasos()`.
- `resources/views/filament/personal/pages/mi-agenda.blade.php` — control
  de botones (Todos/Pendientes/Cerrados) sobre "Mis casos".

(`ProcesoResource.php` y ambos `ClienteResource.php` se probaron primero con
un `SelectFilter` en el panel de filtros; se descartó ese enfoque por pedido
explícito de que los filtros fueran botones visibles arriba de la tabla, no
un desplegable dentro de un panel — de ahí el cambio a `getTabs()` en las
páginas `List*` en vez de tocar los `Resource`.)

### Archivos creados

- `tests/Feature/Net003FiltrosCasosTest.php` — pruebas de la funcionalidad.

## Pruebas realizadas

Suite Pest (`php artisan test --filter=Net003FiltrosCasosTest`), 18 pruebas,
todas en verde. También se corrió la suite completa del proyecto
(`php artisan test`), 40 pruebas, sin regresiones.

| # | Prueba | Resultado |
|---|---|---|
| 1 | Proceso Activo → Pendiente | ✅ |
| 2 | Proceso Cerrado → Cerrado | ✅ |
| 3 | Proceso Archivado → Cerrado | ✅ |
| 4 | Cliente con un caso pendiente → Pendiente | ✅ |
| 5 | Cliente con un caso cerrado → Cerrado | ✅ |
| 6 | Cliente con varios casos (uno pendiente, otros cerrados) → Pendiente | ✅ |
| 7 | Cliente con todos los casos cerrados → Cerrado | ✅ |
| 8 | Cliente sin ningún caso → Cerrado (regla documentada arriba) | ✅ |
| 9 | Filtro Pendientes de Procesos — Admin | ✅ |
| 10 | Filtro Cerrados de Procesos — Admin | ✅ |
| 11 | Filtro Pendientes/Cerrados de Cliente Ejecutivo — Admin | ✅ |
| 12 | Filtro Pendientes/Cerrados de Cliente Ejecutivo — Personal | ✅ |
| 13 | Filtro Pendientes/Cerrados de Mis Casos — Personal | ✅ |
| 14 | Admin y Personal devuelven el mismo resultado sobre el mismo dataset | ✅ |
| 15 | Sin consultas N+1 en filtro de Cliente Ejecutivo (1 query, `whereHas`/`whereDoesntHave`) | ✅ |
| 16 | Sin consultas N+1 en filtro de Procesos (1 query) | ✅ |
| 17 | Regresión: creación de Cliente Ejecutivo directo | ✅ |
| 18 | Regresión: creación de Proceso (default Activo) | ✅ |

No se modificaron reglas de asignación, permisos, ni la relación
Cliente Ejecutivo ↔ Proceso.

## Hallazgos adicionales (NO corregidos en NET-003)

- El panel Personal no tiene un `ProcesoResource` de Filament para "Casos":
  los casos del abogado se listan en una vista Blade simple dentro de la
  página `MiAgenda` (`getCasos()`), sin tabla/filtros nativos de Filament.
  NET-003 asumía la existencia de un "Resource de Casos" en ambos paneles;
  se resolvió agregando un control de filtro simple a esa vista, reutilizando
  los mismos scopes de dominio, en vez de construir un Resource nuevo (fuera
  de alcance de esta tarea).
- El estado `EstadoProceso::Archivado` existe en el enum pero no se asigna
  en ningún flujo actual del sistema (no hay acción de "archivar" un caso
  implementada). Se incluyó en la categoría "Cerrado" por consistencia
  semántica, pero si en el futuro se define un flujo real para Archivado
  convendría revisar si sigue aplicando la misma regla.
- El `ClienteResource` del panel Personal no está filtrado por el abogado
  autenticado (lista todos los Clientes Ejecutivos del sistema, no solo los
  del abogado logueado), a diferencia de "Mis casos" en `MiAgenda`, que sí
  está scoped al `abogado_id`. Es comportamiento preexistente, no modificado.
- No fue posible actualizar documentación en `docs/Dominios/` porque esa
  carpeta (enlace simbólico al Vault de Obsidian, según `CLAUDE.md`) no
  contiene archivos accesibles en este entorno.

## Objetivo

Agregar filtros que permitan distinguir rápidamente los Clientes
Ejecutivos y Casos que todavía tienen trabajo pendiente de aquellos
cuyos casos ya fueron cerrados.

La funcionalidad debe estar disponible tanto para el panel
Administrador como para el panel Personal.

## Alcance

### Cliente Ejecutivo

Agregar un filtro que permita visualizar:

- Pendientes
- Cerrados

La clasificación debe determinarse en función del estado de los
Casos/Procesos asociados al Cliente Ejecutivo.

Un Cliente Ejecutivo debe considerarse:

### Pendiente

Cuando tenga al menos un Caso/Proceso que todavía se encuentre
abierto o en proceso.

### Cerrado

Cuando todos los Casos/Procesos asociados se encuentren cerrados.

La implementación debe analizar primero los estados existentes
en el modelo antes de introducir nuevos estados.

### Casos

Agregar el mismo filtro directamente en el Resource de Casos:

- Pendientes
- Cerrados

En este caso la clasificación corresponde directamente al estado
del Caso/Proceso.

## Panel Administrador

Aplicar los filtros en:

- Cliente Ejecutivo
- Casos/Procesos

## Panel Personal

Aplicar los filtros en:

- Cliente Ejecutivo
- Casos/Procesos

La funcionalidad debe estar disponible para los perfiles que tengan
acceso actualmente a estas secciones, respetando las políticas y
restricciones existentes.

## Regla principal

No crear una lógica independiente para cada panel.

La determinación de "Pendiente" o "Cerrado" debe reutilizar la
misma lógica de dominio/modelo siempre que sea posible.

## Criterios de aceptación

### Cliente Ejecutivo

- [x] Existe filtro de situación (Pendiente/Cerrado), como tabs/botones
      (Todos/Pendientes/Cerrados) arriba de la tabla — no como desplegable,
      por pedido explícito de UX. No se reutilizó la etiqueta "Estado" del
      filtro exacto por enum que ya existía en `ProcesoResource`, para no
      mezclar dos controles con significado distinto bajo el mismo nombre.
- [x] Permite seleccionar Pendientes.
- [x] Permite seleccionar Cerrados.
- [x] Un Cliente Ejecutivo con al menos un caso abierto aparece
      como Pendiente.
- [x] Un Cliente Ejecutivo cuyos casos están todos cerrados aparece
      como Cerrado.
- [x] Un Cliente Ejecutivo con múltiples casos se clasifica
      correctamente.
- [x] El filtro funciona en el panel Administrador.
- [x] El filtro funciona en el panel Personal.

### Casos

- [x] Existe filtro de situación como tabs/botones (mismo criterio que arriba).
- [x] Permite seleccionar Pendientes.
- [x] Permite seleccionar Cerrados.
- [x] Un caso abierto/en proceso aparece como Pendiente.
- [x] Un caso cerrado aparece como Cerrado.
- [x] El filtro funciona en el panel Administrador.
- [x] El filtro funciona en el panel Personal (vía control en "Mis casos"
      de `MiAgenda`; no existe `ProcesoResource` en Personal — ver Hallazgos).

### Regresión

- [x] No modifica los estados existentes.
- [x] No modifica las reglas de asignación.
- [x] No modifica permisos.
- [x] No modifica relaciones entre Cliente Ejecutivo y Casos.
- [x] No duplica la lógica entre Admin y Personal.
- [x] No afecta la creación o edición de Casos.
- [x] No afecta la creación o edición de Clientes Ejecutivos.

## Consideraciones

Antes de implementar:

1. Revisar los estados actuales de Proceso/Caso.
2. Determinar cuál representa realmente "cerrado".
3. Determinar cómo se comporta un Cliente Ejecutivo con múltiples
   Casos.
4. Revisar si existen Clientes Ejecutivos sin ningún Caso.
5. Revisar Resources equivalentes en Admin y Personal.
6. Revisar si ya existe alguna lógica reutilizable para determinar
   el estado del Caso.

No asumir nombres de estados ni crear nuevos estados sin justificación
arquitectónica.

## Pruebas

Debe probarse como mínimo:

1. Cliente Ejecutivo con un caso pendiente.
2. Cliente Ejecutivo con un caso cerrado.
3. Cliente Ejecutivo con varios casos, uno pendiente y otro cerrado.
4. Cliente Ejecutivo con todos los casos cerrados.
5. Caso pendiente.
6. Caso cerrado.
7. Filtro Pendientes en Admin.
8. Filtro Cerrados en Admin.
9. Filtro Pendientes en Personal.
10. Filtro Cerrados en Personal.
11. Regresión de permisos.