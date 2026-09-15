# Decisiones Arquitectónicas — Netley

> Registro estilo ADR (Architecture Decision Record). Las entradas marcadas **"inferida"** se reconstruyeron a partir del código existente (no hay documentación previa ni historial de decisiones registrado); las marcadas **"esta sesión"** son decisiones tomadas y aplicadas directamente durante trabajo con Claude Code.

---

## ADR-001 — Tres paneles Filament con guards de autenticación separados
**Estado:** Implementado · **Origen:** inferida del código (`config/auth.php`, los tres `*PanelProvider`)

**Contexto:** el sistema debe servir a tres tipos de usuario muy distintos: administración interna, staff/abogados, y clientes finales, con permisos y datos visibles completamente diferentes.

**Decisión:** en vez de un único panel con roles/permisos, se crearon **tres paneles Filament independientes** (`admin`, `staff`, `portal`), cada uno con su propio guard (`web`, `personal`, `clientes`) y su propio modelo `Authenticatable` (`User`, `Personal`, `Cliente`).

**Consecuencias:**
- (+) Aislamiento fuerte de acceso: un cliente físicamente no puede alcanzar rutas de Personal o Admin (guard distinto).
- (+) Cada panel puede evolucionar su UI/UX de forma independiente.
- (−) Duplicación de código entre paneles cuando comparten funcionalidad (ver `ConsultaResource` en [[Backlog]]).
- (−) No hay tabla de "roles y permisos" transversal; los tres guards comparten la misma base de datos, así que la separación es de acceso, no de datos.

---

## ADR-002 — Lógica de negocio en el modelo Eloquent, sin capa de Services
**Estado:** Implementado · **Origen:** inferida del código

**Contexto:** el proyecto es una app Filament de tamaño medio, con lógica de negocio concentrada (conversión de consulta a cliente, generación de plan de pagos, generación de credenciales).

**Decisión:** los métodos de negocio viven directamente en los modelos Eloquent (`Cliente::convertirDesdeConsulta()`, `Finanza::generarPlanPagos()`, `Personal::generarAccesoPortal()`, `Proceso::timeline()`, `PlanPago::confirmarPago()`), y las acciones de UI (Filament Actions) los invocan directamente desde closures.

**Consecuencias:**
- (+) Menos indirección para un proyecto de este tamaño; fácil de seguir mientras los modelos no crezcan demasiado.
- (−) Sin capa de Services, la lógica de negocio no es trivialmente reusable fuera de Filament (por ejemplo, desde un comando Artisan o una API futura) sin pasar por el modelo.
- (−) Sin tests automatizados (ver [[Backlog]]), estos métodos de modelo no tienen red de seguridad ante regresiones.

---

## ADR-003 — DomPDF para generación de documentos (contratos, informes)
**Estado:** Implementado · **Origen:** inferida (`app/Filament/Pages/Documentos.php`) + esta sesión (`ConsultaInformeController`)

**Contexto:** el sistema necesita generar documentos descargables: contratos de servicios, fichas de cliente/proceso, planes de pago, e informes de seguimiento de consultas.

**Decisión:** se usa `barryvdh/laravel-dompdf` (ya presente en `composer.json` antes de esta sesión) para renderizar vistas Blade a PDF. En esta sesión se replicó el mismo patrón para el informe de consulta: una única plantilla Blade (`resources/views/consultas/informe.blade.php`) sirve tanto para la vista HTML imprimible como para la generación del PDF, con una variable `$paraPdf` que oculta los botones de acción cuando se renderiza para PDF.

**Consecuencias:**
- (+) Reutilización de la plantilla entre pantalla y PDF; no hay que mantener dos vistas.
- (+) Consistente con el patrón ya existente en `Documentos.php`.
- (−) DomPDF no soporta CSS moderno (flexbox/grid) de forma confiable, por lo que la plantilla usa `<table>` y CSS simple en vez de Tailwind — distinto del resto de la UI (que sí usa Tailwind vía Filament).

---

## ADR-004 — Ruta HTTP dedicada para el informe de consulta, fuera de Filament
**Estado:** Implementado · **Origen:** esta sesión

**Contexto:** se pidió que el informe de una consulta pudiera imprimirse y descargarse en PDF. Los modales de Filament (Livewire) no son adecuados para esto: no generan una URL navegable ni se prestan a `window.print()` limpio ni a servir un archivo binario descargable.

**Decisión:** se creó `ConsultaInformeController` con dos rutas en `routes/web.php` (`consultas/{consulta}/informe` y `.../informe/pdf`), protegidas con middleware `auth` (guard `web`, panel Admin). El botón "Informe" del `ConsultaResource` pasó de abrir un modal (`Action::schema()` con Infolist) a abrir esta URL en pestaña nueva (`->url()->openUrlInNewTab()`).

**Consecuencias:**
- (+) Página real, imprimible de forma nativa por el navegador, y con un endpoint de descarga de PDF independiente.
- (+) Primer controller HTTP "tradicional" del proyecto — precedente para futuras necesidades similares (reportes, exportaciones).
- (−) Rompe la consistencia de "todo vive dentro de Filament"; queda como una excepción documentada.
- (−) La ruta solo está protegida por el guard `web` (Admin) — no fue extendida al panel de Personal, que también tiene la acción "Dar respuesta" pero no un botón de informe equivalente.

---

## ADR-005 — Atribución de respuestas: FK dobles (`personal_id` / `user_id`) en vez de polimorfismo
**Estado:** Implementado · **Origen:** esta sesión

**Contexto:** una respuesta a una consulta (`Answer`) puede darla un administrador (`User`, panel Admin) o un abogado/staff (`Personal`, panel de Personal) — dos modelos `Authenticatable` distintos sin tabla común.

**Decisión:** se agregaron dos columnas nullable (`personal_id`, `user_id`) a `answers` en vez de una relación polimórfica (`morphTo`). Se rellena una u otra según el guard activo al momento de guardar la respuesta.

**Consecuencias:**
- (+) Más simple de leer y de hacer JOIN directo que una relación polimórfica.
- (+) Consistente con el patrón ya usado en `AgendaHistorial.usuario_id` (solo `User`, con un comentario explícito en el código sobre por qué no incluye `Personal`).
- (−) Si en el futuro aparece un tercer tipo de "quien responde" (p. ej. `Cliente`), habría que agregar una tercera columna en vez de que el diseño escale automáticamente.

---

## ADR-006 — Terminología del dominio: se sigue el código, no `CLAUDE.md`
**Estado:** Vigente · **Origen:** esta sesión (siguiendo instrucción explícita del usuario)

**Contexto:** `CLAUDE.md` describe un flujo de dominio (`Ticket`, `Asignación`, `Prospecto`, `Historial`) que no tiene entidades correspondientes en el código real (ver [[Dominios]]).

**Decisión:** toda esta documentación (`docs/`) se escribió reflejando la terminología y las entidades **reales** del código (`Consulta`, `Agenda`, `Proceso`, `Cliente Ejecutivo`, etc.), señalando explícitamente dónde diverge de `CLAUDE.md` en vez de inventar entidades para hacerlas coincidir.

**Consecuencias:**
- (+) La documentación es confiable como reflejo del sistema actual.
- (−) Persiste una discrepancia sin resolver entre `CLAUDE.md` (visión/documentación) y el código — alguien con contexto de negocio debe decidir si `CLAUDE.md` se actualiza o si `Ticket`/`Asignación` son trabajo pendiente de construir.

---

## ADR-007 — Verificación de recibos con HMAC + URL firmada, no firma asimétrica
**Estado:** Implementado · **Origen:** NET-002 (2026-08-11), documentado 2026-09-02 comparando código vs. tarea original

**Contexto:** NET-002 pedía que el QR de un recibo permitiera comprobar que fue "generado por el sistema" y "no fue alterado", sin asumir la solución técnica de antemano (el ticket explícitamente pedía evaluar UUID, HMAC, firma digital o URL de verificación).

**Decisión:** se optó por un esquema simétrico simple: `hash_verificacion` = `hash_hmac('sha256', datos_del_recibo, config('app.key'))`, y el QR codifica una `URL::signedRoute()` de Laravel (firma HMAC de la URL en sí, con expiración/parámetros validados por el framework) en vez de una firma asimétrica (RSA/ECDSA) o un servicio externo de sellado.

**Consecuencias:**
- (+) Sin dependencias nuevas más allá de lo ya usado (`endroid/qr-code`, ya presente para el QR de pagos) y de las utilidades nativas de Laravel (`URL::signedRoute`, `hash_hmac`).
- (+) Suficiente para el requisito real: detectar alteración de los datos del recibo y evitar acceso no autorizado a la URL de verificación.
- (−) `config('app.key')` es la única clave de firma del HMAC. Si `APP_KEY` rota, **todos los recibos emitidos antes dejan de poder autenticarse** (`esAutentico()` devolvería `false`) — no hay versión de clave ni rotación contemplada. Riesgo a tener en cuenta antes de rotar `APP_KEY` en producción.
- (−) No es una firma verificable por un tercero sin acceso al sistema (a diferencia de una firma asimétrica, donde solo se necesita la clave pública) — la verificación solo puede hacerse contra la propia instancia de Netley, lo cual es consistente con el alcance real del ticket (verificar contra "los datos almacenados en Netley"), pero vale la pena señalarlo si en el futuro se requiere verificación independiente del sistema (p. ej. por una entidad externa).

## Ver también

- [[Arquitectura General]]
- [[Dominios]]
- [[Backlog]]
