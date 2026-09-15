# Estado Actual del Sistema — Netley (Laravel)

> **Alcance de este documento:** describe el código realmente existente en `dev/` a fecha 2026-09-15. No describe planes, backlog ni la documentación previa en `docs/` cuando esta no coincide con el código. Ver sección final **"Discrepancia con la documentación previa"** — es la parte más importante de este documento.

## 1. Resumen

Netley es una aplicación **Laravel 12** para la gestión de un despacho/consultorio legal: capta consultas (leads), las agenda, las convierte en clientes con un caso ("Proceso") abierto, gestiona el financiamiento del caso (cuotas), la agenda de citas/llamadas/reuniones del personal, y expone tres paneles independientes:

- **Admin** (`/admin`) — staff administrativo, guard `web`, modelo `User`.
- **Staff / Personal** (`/staff`) — abogados y demás personal operativo, guard `personal`, modelo `Personal`.
- **Portal** (`/portal`) — clientes finales, guard `clientes`, modelo `Cliente`.

La interfaz se construye con **Blade + AdminLTE** (`jeroennoten/laravel-adminlte`), controladores clásicos de Laravel y rutas de recursos (`Route::resource`). **No usa FilamentPHP ni Livewire**, a pesar de que la documentación previa en `docs/` describe una versión basada en Filament (ver sección 10).

## 2. Stack tecnológico

- PHP ^8.2, Laravel ^12.0
- `jeroennoten/laravel-adminlte` — tema de administración basado en Blade (UI real del sistema)
- `barryvdh/laravel-dompdf` ^3.1 — instalado, pero **sin ningún controlador que lo use actualmente**
- `endroid/qr-code` ^6.0 — usado para generar el QR de cada cuota (`PlanPago::generarQr()`)
- Frontend build: Vite 7, Tailwind CSS 4 (sin dependencias JS de runtime más allá de axios)
- Testing: PHPUnit 11.5 (solo tests de ejemplo, sin cobertura de dominio)
- Base de datos: no se especifica motor en este documento; ver `config/database.php` del proyecto para el driver configurado

## 3. Arquitectura de autenticación (multi-guard, multi-panel)

Confirmado en `config/auth.php`: tres guards de sesión, cada uno con su propio *provider* Eloquent, todos sobre la misma base de datos (no hay multi-tenencia, solo separación de acceso):

| Guard | Modelo | Tabla | Acceso |
|---|---|---|---|
| `web` | `App\Models\User` | `users` | Panel Admin — sin restricciones adicionales (`canAccessPanel('admin')` siempre `true` para cualquier usuario autenticado) |
| `personal` | `App\Models\Personal` | `personal` | Panel Staff — requiere `estado === Habilitado` |
| `clientes` | `App\Models\Cliente` | `clientes` | Panel Portal |

Solo existe *password reset broker* para el guard `users`; `personal` y `clientes` no tienen recuperación de contraseña vía email — usan credenciales generadas por el sistema (ver sección 8).

Middleware propio relevante (`dev/app/Http/Middleware`):
- **`EnsurePersonalActive`** (`personal.active`): si el `Personal` autenticado no tiene `estado = Habilitado`, cierra sesión y redirige a `staff.login`.
- **`EnsurePersonalPasswordChanged`** (`personal.password`): si `must_change_password = true`, fuerza redirección a `staff.password.edit` antes de dejar pasar a cualquier otra ruta del panel staff.
- **`SetDefaultGuard`** (`guard.default:{guard}`): fija `Auth::shouldUse($guard)` para que los helpers de blade/AdminLTE resuelvan el guard correcto en cada panel.

## 4. Modelo de dominio

### 4.1 Flujo principal (alto nivel)

```
Consulta (lead)  →  Agenda (cita/llamada)  →  Answer (respuesta)  →  convertir()
                                                                        │
                                                                        ▼
                                                                    Cliente
                                                                        │
                                                                        ▼
                                                                    Proceso (caso)
                                                                    ├── Finanza (1:1) → PlanPago (cuotas, N)
                                                                    ├── Agenda (N)
                                                                    ├── ProcesoDocumento (N)
                                                                    └── DocumentoSolicitud (N) → ProcesoDocumento
```

### 4.2 Modelos (`dev/app/Models`)

**`Consulta`** — el lead/prospecto inicial, sin autenticación.
Campos clave: nombre, apellido_paterno/materno, ci, telefono, whatsapp, `departamento_id`, provincia, pais, email, `materia_legal_id`, descripcion, nota_interna, `origen_id`, colegio_otros, origen_otro, `estado` (enum `EstadoConsulta`), pago_inicial_monto, pago_inicial_registrado_en, `atendido_por` (FK a `Personal`).
Relaciones: `belongsTo` Departamento, MateriaLegal, Origen, Personal; `hasOne` Cliente; `hasMany` Agenda.
Métodos de negocio: `respuestas()` (agrega todas las `Answer` de sus `Agenda`), `ultimaRespuestaLegal()` (última respuesta con categoría Legal, usada para prellenar el alta de caso).

**`Agenda`** — generalización de cita/llamada/reunión.
Campos: tipo (`TipoAgenda`), estado (`EstadoAgenda`), fecha_inicio/fin, asunto, descripcion, modalidad (`ModalidadAgenda`), ubicacion, duracion_minutos, resultado (`ResultadoLlamada`), FKs opcionales a `proceso_id`, `consulta_id`, `cliente_id`, `responsable_id` (Personal), `created_by` (User).
Relaciones: `belongsToMany` Personal vía pivote `agenda_participantes`; `hasMany` Answer, AgendaHistorial.
Lógica: audita automáticamente cada creación/actualización en `AgendaHistorial` (hook `booted()`); `Agenda::hayConflicto()` detecta choques de horario por responsable; scopes `deHoy`, `atrasadas`, `futuras`, `cerradas`.

**`AgendaHistorial`** — log de auditoría de cambios sobre `Agenda` (solo usuarios `User`/admin quedan registrados como autores, nunca `Personal`).

**`Answer`** — respuesta dada en una `Agenda` (típicamente una cita de consulta). Campos: respuesta, categoria (`CategoriaRespuesta`), materia_legal_id, delito_id, publicar (bool), y doble FK de autoría `personal_id`/`user_id` (nunca relación polimórfica).

**`Cliente`** (Authenticatable, guard `clientes`) — cliente final. `consulta_id` es **nullable**: puede crearse por conversión de una `Consulta` (`convertirDesdeConsulta()`) o de forma directa (`crearDirecto()`, usado en alta rápida desde el panel Admin). Usuario/contraseña se autogeneran a partir del teléfono. Relaciones: `hasMany` Proceso, Agenda.

**`Proceso`** — el "caso" legal. Campos: cliente_id, materia_legal_id, tipo_proceso, tiempo_proceso_meses, estado (`EstadoProceso`: Activo/Cerrado/Archivado), abogado_id (Personal). `hasOne` Finanza; `hasMany` Agenda, ProcesoDocumento, DocumentoSolicitud. Método `timeline()`: reconstruye cronológicamente consulta→conversión→apertura→agendas→documentos.

**`Finanza`** — datos financieros de un `Proceso` (relación 1:1). Campos: costo, tipo_pago (`TipoPago`: Semanal/Mensual/AlContado), anticipo, anticipo_registrado_en/confirmado_en. `generarPlanPagos($cuotas, $fechaInicio)`: genera cuotas, limita el número al plazo del proceso, reparte el monto y ajusta el redondeo en la última cuota; bloquea la regeneración si ya hay cuotas pagadas o en confirmación.

**`PlanPago`** — cada cuota. Campos: fecha, monto, estado (`EstadoPago`: Pendiente/PendienteConfirmacion/Pagado/Vencido), qr_path, comprobante, pagado_en. `generarQr()` produce un QR con texto descriptivo (no es un hash/URL de verificación, solo referencia textual del pago). Ciclo: `registrarPagoCliente()` → `confirmarPago()`.

**`Personal`** (Authenticatable, guard `personal`) — staff. Campos notables: `profesiones` y `especialidades` son **arrays JSON** (multi-selección), no columnas enum simples; `estado` (`EstadoPersonal`: solo Habilitado/Deshabilitado); `rol` (`RolPersonal`). Métodos: `esAbogado()`, `esAdministrador()`, `puedeVerCaso(Proceso)` (admin ve todo, abogado solo lo propio), `tieneEspecialidadEnMateria()` (comparación best-effort, con vocabularios no perfectamente alineados — deuda documentada), `generarAccesoPortal()`.

**`ProcesoDocumento`** / **`DocumentoSolicitud`** — documentos adjuntos a un caso y solicitudes de documentos pendientes (con `marcarCumplida()`).

**`Delito`**, **`MateriaLegal`**, **`Origen`**, **`Departamento`** — catálogos administrables (con `activo`, `orden`, `slug`), reemplazando lo que en una versión anterior eran enums de texto libre.

**`User`** — administrador del panel Admin, modelo estándar de Laravel.

### 4.3 Enums (`dev/app/Enums`, 23 en total)

Todos son *backed enums* de PHP con método `label()`. Los más relevantes para entender el negocio:

- **`EstadoConsulta`**: Nueva, Agendada, ReAgendada, ClienteEjecutivo, NoAsistio, Descartada
- **`EstadoAgenda`**: Pendiente, Confirmada, Reagendada, Cancelada, Finalizada
- **`TipoAgenda`**: Cita, Llamada, Reunion
- **`EstadoProceso`**: Activo, Cerrado, Archivado
- **`EstadoPago`**: Pendiente, PendienteConfirmacion, Pagado, Vencido (**nunca se marca automáticamente por ningún job** — deuda pendiente)
- **`TipoPago`**: Semanal, Mensual, AlContado
- **`EstadoPersonal`**: Habilitado, Deshabilitado (solo 2 valores)
- **`RolPersonal`**: Administrador, Abogado, Asistente, Secretaria, Contador, Psicologo, TrabajadorSocial
- **`CategoriaRespuesta`**: Legal, Psicologica, TrabajoSocial, Informatica, Otro
- Especialidades por profesión (usadas para filtrar `especialidades` JSON de `Personal` según su profesión): `EspecialidadAbogado`, `EspecialidadMedica`, `EspecialidadPsicologia`, `EspecialidadTrabajoSocial`
- Resto de catálogos cerrados: `Cargo`, `CategoriaDocumento`, `EstadoCivil`, `EstadoSolicitudDocumento`, `Expedido` (departamentos que expiden CI en Bolivia), `FormaIngreso`, `Genero`, `ModalidadAgenda`, `OrigenDocumento`, `ProfesionPersonal`, `ResultadoLlamada`

## 5. Rutas (`dev/routes`)

**`admin.php`** (prefijo `admin`, nombre `admin.`):
- Auth: `login`/`logout` (guard `web`)
- `admin.dashboard` — contadores (total consultas, total clientes, procesos activos, agenda de hoy)
- CRUD `personal` (sin `show`) + `personal/{id}/resetear-acceso`
- CRUD `consultas` + acciones: `agendar-cita`, `agendar-llamada`, `dar-respuesta`, `convertir`
- CRUD `clientes` (sin `destroy`) + endpoints AJAX `delitos-por-materia`, `abogados-por-materia`
- Casos (`Proceso`) anidados bajo cliente: `clientes/{cliente}/casos/crear|store`, y sueltos: `casos/{proceso}` (show/edit/update), `casos/{proceso}/plan-pagos`
- `cuotas/{planPago}/confirmar`
- CRUD genérico de catálogos: `catalogos/{catalogo}` → `materias-legales`, `origenes`, `departamentos`

**`staff.php`** (prefijo `staff`): login/logout (guard `personal`), cambio de contraseña obligatorio, dashboard.

**`portal.php`** (prefijo `portal`): login/logout (guard `clientes`), dashboard.

**`web.php`**: solo `GET /` (vista `welcome`, sin lógica de negocio) e inclusión de los tres archivos anteriores.

## 6. Controladores — reglas de negocio destacadas

- **`Admin\ConsultaController`**: al ver una consulta calcula flags `puedeAgendarCita`/`puedeAgendarLlamada`/`puedeResponder` (solo una "cita" habilita dar respuesta; no se permite duplicar cita/llamada). Bloquea edición (403) una vez que `estado = ClienteEjecutivo`. Bloquea `destroy` si ya tiene un `Cliente` asociado. `convertir()` replica el flujo legado "ascender caso": crea el `Cliente` y redirige a la creación de `Proceso` prellenando materia/delito desde la última respuesta legal.
- **`Admin\ClienteController@store`**: operación **transaccional** (`DB::transaction`) que crea Cliente + Proceso + Finanza + PlanPago (opcional) en un solo paso, con validación de teléfono boliviano (`^[2-7][0-9]{6,7}$`).
- **`Admin\PersonalController`**: valida que `especialidades` solo contenga valores permitidos según la(s) `profesiones` seleccionadas (mapeo profesión → enum de especialidad correspondiente). Al crear, genera credenciales de portal y las muestra una sola vez (flash).
- **`Admin\ProcesoController@edit`**: la lista de abogados disponibles siempre incluye al abogado actualmente asignado aunque ya no cumpla el filtro de especialidad, para no forzar una reasignación silenciosa.

## 7. Vistas (Blade + AdminLTE)

Tres layouts independientes (`layouts/admin`, `layouts/staff`, `layouts/portal`). Patrón CRUD clásico por carpeta de entidad (`index`, `form` compartido create/edit, `edit`, `show`), con algunas excepciones deliberadas: `personal` no tiene `show`; `procesos` no tiene `index` (solo se accede vía cliente); `catalogos` reutiliza vistas genéricas para los tres catálogos administrables.

## 8. Datos semilla (`database/seeders`)

- 12 materias legales, 13 orígenes (lista legado), 9 departamentos de Bolivia.
- Un `Personal` de prueba (rol Administrador, usuario `70000000`/`password`).
- Un `User` admin (`admin@netley.test` / `password`).

## 9. Deuda técnica y huecos conocidos (observados en el código)

- `EstadoPago::Vencido` nunca se activa automáticamente (no hay job/scheduler que revise cuotas vencidas).
- `Personal::tieneEspecialidadEnMateria()` compara vocabularios (`especialidades` JSON vs. nombre de `MateriaLegal`) que no están 100% alineados.
- `barryvdh/laravel-dompdf` está instalado pero **ningún controlador actual lo usa** (no hay generación de PDF de informes ni contratos en este momento).
- No existe recuperación de contraseña por email para los guards `personal` ni `clientes`.
- Sin cobertura de tests de dominio (solo los tests de ejemplo por defecto de Laravel).

## 10. ⚠️ Discrepancia con la documentación previa (`docs/`)

La documentación existente en `docs/Arquitectura`, `docs/Dominios`, `docs/Procesos`, `docs/base de Datos`, `docs/Decisiones` y `docs/Backlog` describe una **versión distinta y considerablemente más avanzada** del sistema, basada en **FilamentPHP 4** (paneles Admin/Personal/Cliente como Filament Panels, con Resources/Pages/Widgets), e incluye entidades y funcionalidades que **no existen en el código actual (`dev/`)**:

- `Recibo` / `RecibosCorrelativo` — recibos correlativos verificables por QR + hash HMAC-SHA256 (ticket NET-002)
- `Contacto` — agenda de contactos institucionales (NET-005)
- `Instancia` / `Seguimiento` — seguimiento procesal por instancias (NET-007)
- `InformeCierre` y comisiones — cierre formal de caso (NET-008)
- Panel financiero y estadísticas dedicados (NET-009, NET-010)
- Gestor de delitos como módulo propio (NET-011)
- Sitio público (NET-012) y módulo de comentarios (NET-013)
- Matriz de permisos formal (NET-014)
- `ConsultaInformeController` y `ReciboVerificacionController` (controladores públicos fuera de Filament)

El Kanban y el roadmap en `docs/Backlog` marcan los tickets **NET-004 a NET-014 como "Hecho"**, pero ninguno de esos artefactos está presente en `dev/app`, `dev/database/migrations` ni `dev/routes`. Tampoco hay ninguna dependencia de Filament/Livewire en `composer.json`.

**Interpretación:** `docs/` documenta una iteración previa o paralela (probablemente abandonada o en proceso de reescritura hacia Blade+AdminLTE) que no corresponde al estado real del repositorio inspeccionado. Lo que **sí sigue siendo válido** de esa documentación, porque coincide con el código actual:

- El diseño de **tres guards/paneles independientes** sobre las mismas tablas (ADR-001), confirmado en `config/auth.php`.
- El patrón de **"modelo gordo", sin capa de Services** — la lógica de negocio vive en métodos de los modelos Eloquent (ADR-002), confirmado en `Cliente`, `Finanza`, `PlanPago`, `Personal`, `Proceso`, `Agenda`.
- La **autoría dual de `Answer`** vía `personal_id`/`user_id` en vez de relación polimórfica (ADR-005), confirmado en el modelo y la migración.

**Recomendación:** tratar este documento (`Estado Actual del Sistema.md`) como la fuente de verdad sobre lo que existe hoy, y a `docs/` como material histórico/aspiracional hasta que se reconcilie explícitamente con el código o se marque como obsoleto.

---
*Generado a partir de una revisión directa del código en `dev/` (modelos, enums, rutas, controladores, middleware, migraciones, seeders, vistas y configuración de auth) y de los documentos existentes en `docs/`, el 2026-09-15.*
