# Roadmap de Implementación — NET-005 a NET-014

> Hoja de ruta ejecutable, generada a partir de [[Fusión Visión-Actual]] a pedido explícito del usuario (2026-09-03), con pasos técnicos concretos por hito para que Claude Code pueda programar directamente contra ella. No reemplaza a [[Fusión Visión-Actual]] (que documenta el *qué* y el *gap*) — este documento es el *cómo* y el *en qué orden*, sesión a sesión.

## Cómo leer este documento

- Un hito agrupa 1-2 tickets NET-0XX que conviene atacar juntos (por dependencia técnica o porque comparten queries/patrones).
- Cada hito lista objetivo, pasos técnicos concretos, y decisiones que quedaron como **propuesta a confirmar** (marcadas explícitamente) para no bloquear el arranque mientras se espera respuesta del cliente/usuario.
- El estado de avance real se sigue en [[Kanban]], no en este documento — aquí solo vive el orden y el "cómo" de cada hito.

---

## Hito 0 — Completado (2026-09-02)

**NET-004 — Catálogos administrables**: Materias Legales, Orígenes, Departamentos y Delitos ya son tablas administrables. `RolPersonal`/Profesiones quedaron fuera de alcance por acoplamiento de comportamiento. Ver [[NET-004 - Catálogos administrables]].

---

## Hito 1 — NET-014: Matriz de permisos por rol — Hecho (2026-09-03)

El borrador de matriz por rol que se propuso al planear este hito (Administrador/Abogado/Secretaria-Asistente/Contador/Psicologo-TrabajadorSocial, cada uno con su lista de secciones) **no se implementó tal cual**: al revisar el código se encontró que `ConsultaResource` y `MiAgenda` ya están correctamente acotados por asignación (`Agenda.responsable_id`, `Personal.procesos()`), no por rol — y ese mecanismo es justamente lo que hace funcionar el flujo de "Derivar a Psicólogo/Trabajador Social" ya implementado. Restringirlos por rol lo habría roto.

Alcance real implementado — los dos únicos puntos del panel Personal con una diferencia de datos por rol hoy:
- `Personal::esAdministrador()` / `Personal::puedeVerCaso(Proceso $proceso)` — Administrador ve cualquier Caso, Abogado solo el suyo.
- `VerCaso::mount()` — tenía un `abort_unless` **hardcodeado** que dejaba fuera incluso al Administrador (bug real, no solo mejora); corregido para usar `puedeVerCaso()`.
- `ClienteResource` del panel Personal — `canViewAny()`/`canView()`/`canCreate()`/`getEloquentQuery()` sobrescritos directamente en el Resource (no `Filament\Policies`, para no afectar al panel Admin que comparte el modelo `Cliente`). Administrador ve todo, Abogado solo sus clientes, el resto de roles no ve la sección.
- Tests: `tests/Feature/Net014MatrizPermisosTest.php`.

Detalle completo, incluyendo por qué se descartó el borrador original, en [[NET-014 - Matriz de permisos]]. **Patrón a seguir en los hitos siguientes**: cuando NET-005/006/009/010 agreguen Resources nuevos al panel Personal, decidir por caso si el dato ya viene acotado por asignación (no tocar) o si necesita scoping por rol (replicar los overrides directos en el Resource, no Policies de Laravel).

---

## Hito 2 — NET-005 (Contactos) + NET-006 (Llamadas) — Hecho (2026-09-03)

**NET-005 — Contactos**: migración `contactos` (`nombres, apellidos, cargo, profesion, institucion_ministerio, entidad_organo, unidad, telefono, correo, direccion, zona, departamento_id→Departamento, horario_contacto, nota, personal_netley_id→Personal`), modelo, `ContactoResource` — **solo panel Admin** por ahora. `ciudad` del doc viejo se implementó como FK al catálogo `departamentos` de NET-004 en vez de texto libre.

**NET-006 — Llamadas**: sin entidad nueva, `LlamadaResource` filtra `Agenda::where('tipo', Llamada)` con tabs Todas/Diarias/Pendientes/Futuras/Cerradas (mismo patrón de NET-003) — **solo panel Admin** por ahora. 4 scopes nuevos y reutilizables en `Agenda` (`deHoy`, `atrasadas`, `futuras`, `cerradas`).

**Por qué ambos quedaron solo en Admin**: [[Fusión Visión-Actual]] §3 ya señalaba que la exposición de Contactos/Llamadas en el panel Personal "depende de la matriz de permisos de NET-014 — no se define sin esa decisión, para no inventar reglas de negocio". El NET-014 implementado en el Hito 1 no llegó a cubrir estos dos módulos (no existían todavía). Queda pendiente, no descartado — ver "Ver también" en cada ticket.

**Fuera de alcance en este hito** (deferido explícitamente, no fabricado sin confirmar): filtro de Llamadas por materia legal/delito y por departamento, exportar Agenda a Excel, imprimir el listado de Llamadas en PDF. Detalle completo y razones en [[NET-005 - Contactos]] y [[NET-006 - Llamadas]].

**Decisión previa a NET-007 sobre `Instancia`/`Contacto`** (pregunta §10.8 del doc viejo): `Instancia` **no** reutiliza la tabla `Contacto` — son conceptos distintos (Contacto = persona externa; Instancia = ubicación procesal del caso). En su lugar, `Instancia` tendrá un `contacto_id` nullable opcional para vincular al juez/fiscal si aplica. Sigue como propuesta a confirmar — detalle en [[NET-007 - Instancias y Seguimientos]].

---

## Hito 3 — NET-007 (Instancias/Seguimientos) + NET-008 (Cierre formal + Comisiones) — Hecho (2026-09-03)

**NET-007**: migraciones `instancias` (`caso_id, tipo[Juzgado/Fiscalía/Otro], datos, contacto_id nullable`) y `seguimientos` (`caso_id, instancia_id, personal_id nullable, fecha, hora, detalle`) + `InstanciasRelationManager`/`SeguimientosRelationManager` en `ProcesoResource` (Admin) + dos acciones nuevas (`agregarInstanciaAction`, `agendarSeguimientoAction`) en `VerCaso` (Personal), con sección nueva en su vista.

**NET-008**: migración `informe_cierres` + columnas nuevas en `Proceso` (`comision_bs, comision_pct, tipo_referido`) + acción "Cerrar caso" en `ProcesoResource` (crea el informe y cambia `Proceso.estado` a Cerrado en un solo paso) + `InformeCierreController`/vista imprimible reutilizando el patrón DomPDF de `ConsultaInformeController` (ADR-003/004). El wizard `MiAgenda::cierreAction()` no se tocó, sigue siendo el disparador del día a día.

**Decisión que se apartó del doc viejo**: `Proceso`→`InformeCierre` se implementó `hasMany` (histórico) en vez de 0–1, para no perder el informe anterior cuando un caso se reactiva y se vuelve a cerrar (`Proceso::ultimoInformeCierre()` trae el más reciente).

**Hallazgo colateral, documentado en [[Backlog]] y no corregido en este hito** (fuera de alcance): las rutas HTTP dedicadas fuera de Filament (`ConsultaInformeController` y el nuevo `InformeCierreController`) devuelven 500 en vez de redirigir cuando un visitante no autenticado las visita — el middleware `auth` por defecto apunta a una ruta `login` que no existe en esta app (cada panel Filament tiene la suya con otro nombre). Preexistente, compartido por ambos controllers, no introducido por NET-008.

Detalle completo en [[NET-007 - Instancias y Seguimientos]] y [[NET-008 - Cierre formal de Caso y Comisiones]].

---

## Hito 4 — NET-009 (Panel Financiero) + NET-010 (Estadísticas) — Hecho (2026-09-03)

Sin tablas nuevas. `PanelFinanciero` (NET-009, con filtros año/mes/sucursal/abogado) y `Estadisticas` (NET-010, sin filtros propios, los 11 reportes del doc viejo) como páginas custom del panel Admin.

**Hallazgos que obligaron a decisiones de alcance, no anticipados al planear este hito**:
- "Sucursal" no existe como campo propio en `Proceso`/`Finanza`/`Recibo` — ambos tickets usan el `departamento_id` del abogado asignado (de NET-004) como proxy.
- `EstadoPago::Vencido` nunca lo asigna ningún job/comando (ver [[Backlog]]) — la mora se calcula por fecha (`PlanPago::enMora()`/`porVencer()`), no por ese enum, o el reporte habría salido siempre vacío.
- `Proceso` no tiene una fecha límite propia (el doc viejo sí tenía `fecha_fin`, nunca se creó en Netley) — "casos próximos a vencer"/"vencidos" (NET-010, reporte 10) se derivan de `created_at + tiempo_proceso_meses` (`Proceso::scopeProximosAVencer()`/`scopeVencidos()`, accessor `fecha_estimada_fin`).
- "Promedio de días para concluir un caso" (NET-010, reporte 7) solo cuenta casos con `InformeCierre` formal (NET-008) — los cerrados antes, o vía el wizard simple de `MiAgenda`, no tienen una fecha de cierre confiable y quedan fuera.

No se implementó cache de agregaciones (recomendado por el doc viejo §8) — sin datos reales de volumen contra qué decidir la estrategia. Detalle completo en [[NET-009 - Panel de Control Financiero]] y [[NET-010 - Estadísticas]].

---

## Hito 5 — NET-011: Gestor de Delitos — Hecho (2026-09-03)

**Hallazgo previo a implementar**: `Answer` (el `Respuesta` real de Netley) no tenía ningún campo de clasificación — el ticket asumía que `delito_denuncia_id` ya existía (heredado del doc viejo), pero nunca se construyó. Se agregó `answers.delito` como **texto libre**, no FK a `delitos` — mismo patrón ya usado en `Proceso.tipo_proceso` (`Delito::opcionesPara()` como catálogo de sugerencias, no relación estricta).

`App\Filament\Pages\GestorDelitos` (Admin): tabla de `Answer::sinClasificar()` + acción "Clasificar" (Select filtrado por materia legal de la Consulta, con opción de agregar un Delito nuevo). La auto-clasificación (heurística o IA) queda **fuera de alcance**, tal como pedía el ticket, hasta confirmar con el cliente. Detalle en [[NET-011 - Gestor de Delitos]].

---

## Hito 6 — NET-012 (Sitio público) + NET-013 (Comentarios) — Hecho (2026-09-03)

Construido con Controllers + Blade (no Livewire), única parte fuera de los 3 paneles Filament, compartiendo la misma BD. Home real (reemplaza el `welcome.blade.php` default), Consulta Gratuita (con captcha matemático simple, sin servicio externo), Únete a Netley, Publicaciones (CRUD simple, no CMS), Testimonios moderados vía NET-013 (Comentario como entidad independiente alimentada por el sitio, no vinculada a `Answer.publicar`).

**Sin bandeja de revisión para Postulaciones** — a propósito, el doc viejo deja eso sin confirmar (§10.7); se persisten los envíos para no perderlos, sin construir UI de moderación sin esa decisión. **Sin CMS real para Publicaciones** — CRUD simple, interpretación mínima ante la pregunta sin confirmar (§10.6). "Quiénes somos"/"Contacto" del menú viejo no se construyeron — no están en las HU de la Épica 13.

**Bug real encontrado y corregido**: `Publicacion`/`Postulacion` no declaraban `$table`, y el pluralizador de Eloquent adivina mal esas palabras en español (mismo problema, y mismo fix, que ya existía en `OrigenResource` para el *slug* de Filament). Detalle completo en [[NET-012 - Sitio público]] y [[NET-013 - Comentarios]].

---

## Estado del roadmap: completo (2026-09-03)

Las 14 épicas de `VISION_Y_REQUERIMIENTOS_NETLEY_FILAMENT.md` quedaron cubiertas por NET-001 a NET-014. No quedan tickets nuevos de este roadmap por arrancar — lo que sigue son las preguntas abiertas heredadas del doc viejo (§10, ver cada ticket "Hecho" para el detalle de cuáles siguen sin responder) y la deuda técnica de [[Backlog]].

---

## Deuda técnica a resolver de paso

No son hitos propios — se arreglan al tocar cada área, no ameritan un sprint dedicado (ver [[Backlog]]):
- `ConsultaResource` duplicado entre Admin y Personal.
- Mensajes de validación mezclando idiomas (inglés/español).
- Falta de tests en Consulta, Agenda y `Finanza::generarPlanPagos()`.

---

## Ver también
- [[Fusión Visión-Actual]]
- [[Kanban]]
- [[Backlog]]
- [[Decisiones Arquitectónicas]]
- `VISION_Y_REQUERIMIENTOS_NETLEY_FILAMENT.md` (raíz del repo)
