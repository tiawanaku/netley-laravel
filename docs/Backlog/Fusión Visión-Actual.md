# Fusión: Visión del sistema viejo vs. Netley actual

> Cruza `VISION_Y_REQUERIMIENTOS_NETLEY_FILAMENT.md` (raíz del repo — levantamiento por ingeniería inversa del panel legado PHP, 14 épicas) contra el estado real del código documentado en [[Dominios]], [[Arquitectura General]], [[Procesos]] y [[Backlog]] (todos verificados contra código, actualizados 2026-09-02). Objetivo: que el modelo de negocio y la navegación del sistema viejo terminen replicados en Netley actual, entidad por entidad y pantalla por pantalla. Generado 2026-09-02 a pedido explícito del usuario.

## Cómo leer este documento

- **Tabla de entidades**: qué existía en el sistema viejo, su equivalente real hoy (si existe) y el gap concreto.
- **Tabla de épicas**: las 14 épicas del doc viejo, su estado actual y el ticket NET-XXX que la cerraría.
- **Navegación objetivo**: cómo debería quedar el menú de cada panel una vez cerrados los tickets.
- **Roadmap**: orden sugerido de ataque.
- **Preguntas abiertas**: heredadas del doc viejo (§10), siguen sin respuesta del cliente.

---

## 1. Entidades: sistema viejo → Netley actual

| Entidad (sistema viejo, §4.1) | Equivalente real hoy | Estado | Gap |
|---|---|---|---|
| **Personal** | [[Personal]] | Implementado | Sin `profesión` multi-valor, `ci_expedido` (catálogo departamento), `foto`, curriculum (PDFs) — ver Épica 2 |
| **Contacto** | — | **No existe** | Directorio externo completo (jueces, fiscales, instituciones) → NET-005 |
| **Consulta** | [[Consulta]] | Implementado | Coincide bien; añade `origen`/`forma_ingreso` con más detalle que el viejo |
| **Respuesta** | [[Answer]] | Implementado (simplificado) | Sin `publicar` (testimonio), sin `categoría` (Legal/Psicológica/Trabajo Social/Informática/Otro) — evaluar si suma a NET-013 (Comentarios) |
| **Cliente** | [[Cliente Ejecutivo]] | Implementado | Sin `sucursal`, sin `correo` propio (usa el de la Consulta origen) |
| **Caso/Proceso** | [[Proceso]] | Implementado (núcleo) | Sin `apersonamiento`, `sucursal/oficina`, comisión/referido → NET-008 |
| **Instancia** (Juzgado/Fiscalía/Otro) | — | **No existe** | → NET-007 |
| **Seguimiento** (sobre Instancia) | — | **No existe** | → NET-007 |
| **Pago** | [[Finanza]] + [[Recibo]] | Implementado (superado) | El sistema actual ya tiene correlativo + hash HMAC verificable por QR (NET-002); el viejo solo generaba PDF simple |
| **Cuota** | [[PlanPago]] | Implementado (superado) | Emisión de recibo automática al pagar, QR de referencia; el viejo no tenía pantalla clara de generación de cuotas |
| **Documento** | `ProcesoDocumento` + `DocumentoSolicitud` | Implementado (superado) | El actual ya tiene flujo de solicitud staff→cliente que el viejo no tenía explícito |
| **Cita** | [[Agenda]] (`tipo=Cita`) | Implementado | Coincide, con reglas de horario laboral que el viejo no documentaba |
| **Llamada** | [[Agenda]] (`tipo=Llamada`) | Parcial | Existe el tipo, pero sin Resource/listados dedicados (Diarias/Pendientes/Futuras/Cerradas) → NET-006 |
| **InformeCierre** | — | **No existe** | Hoy el cierre de un Caso solo cambia `Proceso.estado`, sin informe estructurado imprimible → NET-008 |
| **Comentario** (testimonio) | — | **No existe** | → NET-013 |
| **Publicación** (blog/CMS) | — | **No existe** | → NET-012 |
| **Postulación** ("Únete a Netley") | — | **No existe** | → NET-012 |

Entidades que existen hoy **sin equivalente en el sistema viejo** (mejoras propias de la reconstrucción): `User` (guard Admin dedicado — el viejo tenía un único login con `Nivel de acceso`), `AgendaHistorial` (auditoría de cambios), `Recibo` (verificación pública por QR+hash).

---

## 2. Épicas: estado y ticket que las cierra

| Épica (doc viejo) | Estado actual | Ticket |
|---|---|---|
| 0 — Autenticación y Control de Acceso | Parcial — 3 guards/paneles implementados (ADR-001), **sin matriz de permisos por Cargo/Nivel** | **NET-014** |
| 1 — Dashboard | Parcial — calendario existe; faltan los 4 bloques de alertas (Vencidos, Próximos a Vencer, Citas/Llamadas de hoy, Mora) | Se resuelve reutilizando las queries de NET-009/NET-010, sin ticket propio |
| 2 — Personal (RR.HH.) | Implementado — falta Curriculum (subida de PDFs) | Gap menor, sumar a NET-005 si se prioriza |
| 3 — Contactos | No existe | **NET-005** |
| 4 — Consultas | Implementado (mejorado: informe PDF con línea de tiempo) | — |
| 5 — Cliente Ejecutivo (núcleo) | Parcial — Cliente/Proceso/Pagos/Documentos/Citas ya están; faltan Instancias/Seguimientos y Cierre formal | **NET-007**, **NET-008** |
| 6 — Pagos y Control Financiero | Parcial — el registro de pagos ya existe (superado); falta el panel de reportes agregados | **NET-009** |
| 7 — Agenda | Implementado (calendario Filament) — exportar a Excel falta, `AgendaResource` no está en el menú (ya en [[Backlog]]) | Gap menor, sumar a NET-006 |
| 8 — Llamadas | Parcial vía `Agenda.tipo=Llamada` | **NET-006** |
| 9 — Estadísticas | No existe | **NET-010** |
| 10 — Comentarios | No existe | **NET-013** |
| 11 — Gestor de Delitos | No existe (`Delito` es solo catálogo simple, sin cola de clasificación) | **NET-011** |
| 12 — Catálogos administrables | **Implementado parcialmente** (2026-09-02) — Materias Legales, Orígenes, Departamentos y Delitos ya son tablas administrables; Cargos/Profesiones (`RolPersonal`) quedaron fuera por acoplamiento de comportamiento | **NET-004** (hecho) |
| 13 — Sitio público | No existe (`/` sirve el `welcome.blade.php` default de Laravel) | **NET-012** |

---

## 3. Navegación objetivo por panel

El sistema viejo tenía un único panel con menú plano (§3.2). Netley ya decidió (ADR-001) separar en 3 paneles Filament + guard propio — esa decisión **no se revierte**; la navegación del viejo se redistribuye entre los 3 paneles según quién opera cada sección.

**Admin (`/admin`)** — grupos de navegación objetivo:
```
Dashboard
Personal                    (ya existe)
Contactos                   (nuevo — NET-005)
Consultas                   (ya existe)
Cliente Ejecutivo           (ya existe: Casos, Pagos, Documentos)
  └─ Instancias/Seguimientos (nuevo — NET-007, dentro del Caso)
  └─ Cierre de Caso          (nuevo — NET-008)
Pagos → Panel Financiero    (nuevo — NET-009; el registro de pagos ya vive dentro de Caso)
Agenda                      (ya existe — falta exponerla en el menú)
Llamadas                    (nuevo/parcial — NET-006)
Estadísticas                (nuevo — NET-010)
Comentarios                 (nuevo — NET-013)
Gestor de Delitos           (nuevo — NET-011)
Catálogos                   (hecho — NET-004: Materias Legales, Orígenes, Departamentos)
Recibos                     (ya existe)
```

**Personal (`/staff`)** — subconjunto operativo (Mi Agenda, Consultas, Cliente Ejecutivo ya existen). Qué más se expone aquí (¿Contactos? ¿Llamadas? ¿Estadísticas propias?) **depende de la matriz de permisos de NET-014** — no se define sin esa decisión, para no inventar reglas de negocio.

**Cliente (`/portal`)** — sin cambios de alcance; el sistema viejo no tenía portal de cliente, esto ya es una mejora propia de Netley.

**Sitio público (nuevo, fuera de los paneles Filament)** — Home con Consulta Gratuita, Únete a Netley, Publicaciones, Testimonios → NET-012/NET-013. Comparte la misma base de datos (Eloquent), construido aparte (Livewire/Blade), tal como recomienda el doc viejo (§4.2 Épica 13).

---

## 4. Roadmap sugerido

Orden por dependencia, no por tamaño fijo de sprint (mismo criterio que el doc viejo §7):

1. **NET-014** (matriz de permisos) — desbloquea decisiones de navegación de Personal y visibilidad de Estadísticas/Financiero. Transversal, conviene resolverla temprano aunque no sea la más vistosa.
2. ~~**NET-004** (catálogos administrables)~~ — **hecho** (2026-09-02): Materias Legales, Orígenes, Departamentos y Delitos. Cargos/Profesiones (`RolPersonal`) quedaron fuera de alcance, ver [[NET-004 - Catálogos administrables]].
3. **NET-005** (Contactos) y **NET-006** (Llamadas) — módulos nuevos, bajo riesgo, no tocan las entidades ya construidas.
4. **NET-007** (Instancias/Seguimientos) y **NET-008** (Cierre formal + Comisiones) — extienden el núcleo Cliente Ejecutivo/Proceso ya construido.
5. **NET-009** (Panel Financiero) y **NET-010** (Estadísticas) — comparten queries agregadas, conviene desarrollarlos juntos.
6. **NET-011** (Gestor de Delitos) — depende de que NET-004 ya tenga el catálogo de Delitos completo.
7. **NET-012** (Sitio público) y **NET-013** (Comentarios) — última etapa, es la única parte fuera de los paneles Filament.

---

## 5. Contradicciones y preguntas abiertas heredadas

- **ADR-006 sigue sin resolverse**: `CLAUDE.md` describe un dominio (`Ticket`, `Asignación`, `Prospecto`, `Historial`) que **no existe ni en el código ni en el sistema viejo real**. Esta fusión no lo resuelve — el usuario no pidió tocar `CLAUDE.md` en esta sesión. Queda como decisión pendiente: actualizar `CLAUDE.md` a la terminología real, o tratar `Ticket`/`Asignación` como un rediseño futuro genuino (no visto en ningún sistema, ni viejo ni actual).
- Las 10 preguntas abiertas del doc viejo (§10) **siguen sin respuesta del cliente** y condicionan varios tickets de arriba, en particular:
  - Matriz de permisos por Nivel de acceso → bloquea **NET-014**.
  - Enum completo de `Estado` de Caso y Consulta → afecta **NET-004** y el propio `Proceso`/`Consulta` ya existentes.
  - Si "Instancia" comparte catálogo con "Contacto" → afecta el diseño de **NET-005** y **NET-007** (evitar construirlos como silos si en realidad son la misma fuente de datos).
  - Origen y propósito del texto **"Stop Claude"** hallado en el HTML del panel viejo (§0 del doc viejo) — hallazgo de seguridad, no accionado, pendiente de que el equipo audite el código fuente original.

---

## Ver también

- `VISION_Y_REQUERIMIENTOS_NETLEY_FILAMENT.md` (raíz del repo)
- [[Dominios]]
- [[Arquitectura General]]
- [[Procesos]]
- [[Backlog]]
- [[Decisiones Arquitectónicas]]
- [[Roadmap de Implementación]] — pasos técnicos concretos por hito, generado 2026-09-03 a partir de este documento
