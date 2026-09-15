# Backlog técnico — Netley

> Hallazgos reales, observados en el código y/o durante pruebas manuales del sistema (no son ideas nuevas inventadas). Cada ítem indica cómo se detectó. Actualizado 2026-08-10; revisado contra el código el 2026-09-02 (ver sección "Revisado 2026-09-02" más abajo).

## Resueltos en esta sesión

- [x] **Sin forma de ver las respuestas dadas a una consulta.** La acción "Dar respuesta" guardaba en `answers` pero no había ningún lugar del panel donde volver a leerlas. → Se agregó el botón "Informe" en `ConsultaResource` (Admin) con vista imprimible y descarga PDF. Ver [[Procesos]].
- [x] **No se registraba quién respondía una consulta.** La tabla `answers` no tenía columna para el autor. → Se agregaron `personal_id` y `user_id` (migración `2026_08_10_161705_add_responder_to_answers_table`).
- [x] **`especialidad_abogado` no era obligatorio para Abogados.** Ver [[NET-001 - Especialidad abogado obligatoria]] (2026-08-10) — ahora es obligatorio en `PersonalResource` cuando `rol = Abogado`, con mensaje de validación claro. El hallazgo relacionado (enum `EspecialidadAbogado` con más valores que `CategoriaLegal`) sigue sin resolver, queda como candidato a un ticket separado.
- [x] **`APP_URL` mal configurado** para el entorno de desarrollo con Apache/XAMPP en subcarpeta, rompía la carga de Livewire en todo el panel Admin. → Corregido en `.env` + `URL::forceRootUrl()` en `AppServiceProvider`, y se optó por usar `php artisan serve` en el puerto 8000 para evitar el problema de subcarpeta.

## Pendientes — Datos / Reglas de negocio

- [ ] **`EspecialidadAbogado` tiene más valores que `CategoriaLegal`** (Constitucional, Ambiental, Minero, Migratorio, Propiedad Intelectual no existen en `CategoriaLegal`). Un abogado con esas especialidades nunca podrá recibir casos, porque ni `Consulta.tipo_proceso` ni `Proceso.materia_legal` pueden tomar esos valores. *(Detectado comparando ambos enums.)*
- [ ] **`EstadoPago::Vencido` no tiene lógica automática que lo dispare.** No se encontró ningún job, comando programado (`app/Console`) ni listener que revise fechas vencidas de `plan_pagos` y actualice su estado. *(Búsqueda en el código — no se halló implementación.)*
- [ ] **Búsqueda del selector "Proceso/Caso" en el formulario de Agenda no coincide con lo que se muestra.** El campo es `searchable(['tipo_proceso'])`, pero la etiqueta mostrada al usuario es `"{cliente} — {tipo_proceso}"` (vía `getOptionLabelFromRecordUsing`). Buscar por el nombre del cliente no encuentra resultados. *(Reproducido en pruebas QA manuales.)*

## Pendientes — Infraestructura (nuevo, 2026-09-03)

- [ ] **Las rutas HTTP dedicadas fuera de Filament (`ConsultaInformeController`, y el nuevo `InformeCierreController` de NET-008) devuelven 500 en vez de redirigir al login cuando el visitante no está autenticado.** Ambas usan `Route::middleware('auth')`, cuyo comportamiento por defecto es redirigir a una ruta nombrada `login` — que no existe en esta app (cada panel Filament registra su propio login bajo otro nombre, p. ej. `filament.admin.auth.login`). Detectado al escribir el test de NET-008 (`tests/Feature/Net008CierreFormalTest.php`), no corregido en esa sesión porque decidir a qué login redirigir (¿siempre Admin? ¿según qué guard tenía sesión?) es una decisión que excede el ticket puntual.

## Pendientes — Duplicación / mantenibilidad

- [ ] **`ConsultaResource` está duplicado casi por completo entre Admin y Personal** (`app/Filament/Resources/Consultas/ConsultaResource.php` vs `app/Filament/Personal/Resources/Consultas/ConsultaResource.php`), incluyendo las acciones `agendarCita` y `darRespuesta`. Sin trait/servicio compartido. Ya causó que un fix (el tracking de "quién respondió") tuviera que aplicarse manualmente en dos archivos. *(Comparación directa de ambos archivos.)*
- [ ] **`AgendaResource` no aparece en el menú de navegación** del panel Admin — solo es alcanzable indirectamente a través del widget de calendario del Dashboard. No hay una forma directa de listar/filtrar todas las agendas desde el menú. *(Verificado navegando el panel.)*

## Pendientes — Calidad / Testing

- [ ] **Cobertura de tests parcial.** `tests/Feature/` tiene `Net003FiltrosCasosTest.php` (18 pruebas, filtros Pendientes/Cerrados) y `ReciboTest.php` (NET-002), además de los ejemplos por defecto de Laravel. Consulta, Agenda, conversión a Cliente y generación de plan de pagos (`Finanza::generarPlanPagos()`) siguen sin tests automatizados. *(Listado directo de `tests/`, actualizado 2026-09-02 — antes decía "sin cobertura", ya no es exacto.)*
- [ ] **Mensajes de validación mezclando idiomas.** Algunos mensajes de error y notificaciones de éxito aparecen en inglés ("The teléfono field format is invalid.", "Created") mientras el resto de la interfaz está en español. *(Observado en pruebas QA manuales.)*
- [ ] **Formulario de subida de documento de proceso no muestra error visible** cuando falta el archivo obligatorio — bloquea el envío sin feedback visual claro para el usuario. *(Observado en pruebas QA manuales.)*

## Pendientes — Documentación / Proceso

- [x] **El symlink `docs` → vault de Obsidian estaba roto en esta PC.** Git lo había checkeado como archivo de texto plano apuntando a una ruta de otra máquina (`C:/Users/Display/Mi unidad/...`), inexistente aquí. Reconstruido el 2026-09-02 como junction NTFS hacia `G:\My Drive\franz life\Franz's life\🚀 Proyectos\Netley`. **Es un fix local de esta máquina, no versionado en git** (el symlink es inherentemente específico de cada PC); si se trabaja desde otra máquina, hay que rehacerlo apuntando a la ruta del vault en esa PC.
- [ ] **`CLAUDE.md` describe un flujo de dominio (`Ticket`, `Asignación`, `Prospecto`, `Historial`) que no coincide con las entidades reales del código.** Ver detalle en [[Dominios]] y [[Decisiones Arquitectónicas]] (ADR-006). Sigue sin resolverse: debería decidirse si se actualiza `CLAUDE.md` a la terminología real, o si esos conceptos son un rediseño pendiente de implementar.
- [ ] **Archivos no estándar en la raíz del repo** (`agents/1g.php`, carpeta `graphify-out/`) no parecen parte de la app Laravel — no se determinó su propósito actual ni si deben seguir versionados.

## Revisado 2026-09-02

- **NET-002 (Recibo de pago correlativo y QR verificable) ya está implementado**, no es Backlog como decía la tarea original — commit `d0b4180` (2026-08-11). Ver [[NET-002 - Recibo de pago correlativo y QR verificable]] y [[Recibo]] en [[Dominios]]. Quedó un criterio sin confirmar (búsqueda/filtro por número/cliente/período en `ReciboResource`).
- **NET-003 (filtros Pendientes/Cerrados) confirmado implementado y testeado** en el código (commit `d9d6395`), consistente con lo que ya documentaba [[NET-003 - Filtros casos pendientes y cerrados]].

## Pendientes — Infraestructura

- [ ] **Configuración actual apta solo para desarrollo.** El proyecto corre vía `php artisan serve` en el puerto 8000 en lugar de un VirtualHost de Apache apuntando a `public/`; no hay documentación de despliegue a producción.
- [x] **Puesta en marcha en una PC nueva (checklist, verificado 2026-09-02).** `.env` no está versionado (correcto) pero tampoco hay ninguna nota de qué pasos hacen falta; en esta sesión el checkout nuevo dio 500 por dos causas encadenadas:
  1. Sin `.env` → `MissingAppKeyException` (500 "silencioso": sin `.env`, Laravel arranca en `APP_ENV=production` por defecto, así que no muestra el detalle en pantalla — solo queda en `storage/logs/laravel.log`).
  2. Sin `npm install && npm run build` → `Vite manifest not found` en cuanto Filament intenta renderizar cualquier panel.

  Checklist para levantar el proyecto en una PC sin configurar:
  ```
  cp .env.example .env
  # editar DB_DATABASE si la base local no se llama "netley"
  # editar APP_URL si no se usa php artisan serve en :8000
  php artisan key:generate
  php artisan migrate
  php artisan storage:link
  npm install
  npm run build
  php artisan serve
  ```
  Nota: `DB_DATABASE` en `.env.example` es `netley`, pero la base real usada en desarrollo (XAMPP/MySQL local) es `netley-oficial` — revisar con `SHOW DATABASES;` antes de asumir el nombre.

## Épicas del sistema viejo no replicadas

Agregado 2026-09-02, a pedido explícito del usuario: `VISION_Y_REQUERIMIENTOS_NETLEY_FILAMENT.md` (raíz del repo) documenta 14 épicas del sistema legado; Netley actual cubre un subconjunto. Ver el mapeo completo entidad por entidad y épica por épica en [[Fusión Visión-Actual]], con 11 tickets nuevos de backlog: [[NET-004 - Catálogos administrables]], [[NET-005 - Contactos]], [[NET-006 - Llamadas]], [[NET-007 - Instancias y Seguimientos]], [[NET-008 - Cierre formal de Caso y Comisiones]], [[NET-009 - Panel de Control Financiero]], [[NET-010 - Estadísticas]], [[NET-011 - Gestor de Delitos]], [[NET-012 - Sitio público]], [[NET-013 - Comentarios]], [[NET-014 - Matriz de permisos]].

Agregado 2026-09-03, a pedido explícito del usuario: la secuencia de ataque de esos 11 tickets (con pasos técnicos concretos por hito) se plasmó en [[Roadmap de Implementación]], para poder arrancar a programar hito a hito a partir de hoy.

**NET-014 (Hito 1) hecho el mismo día (2026-09-03)**: matriz de permisos del panel Personal, con un hallazgo relevante — el borrador de matriz por rol planeado inicialmente habría roto el flujo de "Derivar a Psicólogo/Trabajador Social" si se aplicaba tal cual (`ConsultaResource`/`MiAgenda` ya están acotados por asignación, no por rol). Se implementó en su lugar el scoping donde realmente hacía falta (`VerCaso`, `ClienteResource` del panel Personal) y se corrigió de paso un bug real: `VerCaso` dejaba fuera al rol Administrador por un `abort_unless` hardcodeado a "solo el abogado asignado". Detalle en [[NET-014 - Matriz de permisos]].

## Ver también

- [[Arquitectura General]]
- [[Dominios]]
- [[Decisiones Arquitectónicas]]
- [[Fusión Visión-Actual]]
- [[Roadmap de Implementación]]
