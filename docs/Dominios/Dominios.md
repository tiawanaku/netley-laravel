# Dominios — Netley

> Dominios reales identificados en el código (`app/Models`), no el listado aspiracional de `CLAUDE.md`. Ver contraste al final de esta nota.

## [[Consulta]]

Primer contacto / intake ("prospecto"). Modelo `App\Models\Consulta`, tabla `consultas`.

- Datos de contacto: nombre, apellidos, CI, teléfono, WhatsApp, email, ciudad.
- Datos de la consulta: `tipo_proceso` (enum `CategoriaLegal`), `descripcion`, `origen` (enum `OrigenConsulta`), `forma_ingreso` (enum `FormaIngreso`), `colegio_otros`.
- `estado` (enum `EstadoConsulta`): Nueva, Agendada, ReAgendada, ClienteEjecutivo, NoAsistió, Descartada.
- `atendido_por` → [[Personal]] (quién tomó/atendió la consulta; solo se completa desde el flujo "Mi Agenda" del portal de Personal, no desde el panel Admin).
- `pago_inicial_monto` / `pago_inicial_registrado_en` — registro de un pago inicial asociado a la consulta.
- Relaciones: `hasOne` [[Cliente Ejecutivo]] (vía `convertirDesdeConsulta`), `hasMany` [[Agenda]].

## [[Agenda]]

Citas, llamadas y reuniones. Modelo `App\Models\Agenda`, tabla `agendas`. Es el concepto más cercano a la "Cita" de `CLAUDE.md`, pero generalizado a tres tipos.

- `tipo` (enum `TipoAgenda`): Cita, Llamada, Reunión.
- `estado` (enum `EstadoAgenda`): Pendiente, Confirmada, Reagendada, Cancelada, Finalizada.
- `modalidad` (enum `ModalidadAgenda`: Presencial/Virtual), `ubicacion`, `duracion_minutos`, `resultado` (enum `ResultadoLlamada`, solo para llamadas).
- Se vincula opcionalmente a **uno** de: [[Proceso]] (`proceso_id`), [[Consulta]] (`consulta_id`) o [[Cliente Ejecutivo]] (`cliente_id`), según el contexto en que se creó.
- `responsable_id` → [[Personal]] asignado.
- `created_by` → `User` (obligatorio, admin que la creó).
- `participantes` — relación N:N con [[Personal]] vía tabla pivote `agenda_participantes` (usada para reuniones con varios asistentes).
- Cada creación/actualización queda auditada en **[[AgendaHistorial]]** (tabla `agenda_historiales`, con `datos_anteriores`/`datos_nuevos` en JSON).
- `Agenda::hayConflicto()` — valida solapamiento de horario para un mismo `responsable_id`.

## [[Answer]]

Respuestas dadas durante una cita ligada a una consulta. Modelo `App\Models\Answer`, tabla `answers`. Dominio pequeño, ampliado en esta sesión.

- Pertenece a una [[Agenda]] (`agenda_id`, tipo Cita).
- `respuesta` (texto).
- `personal_id` / `user_id` (nullable, agregados 2026-08-10) → quién respondió, sea desde el portal de Personal o desde el panel Admin.
- Accesible desde `Consulta::respuestas()`, que arma los datos combinados de "quién tomó la cita + quién respondió + tiempo transcurrido" para el informe imprimible/PDF de una consulta.

## [[Cliente Ejecutivo]]

El cliente ya convertido. Modelo `App\Models\Cliente` (`Authenticatable`, guard `clientes`), tabla `clientes`.

- Puede originarse de una [[Consulta]] (`consulta_id`, nullable desde 2026-08-04 — permite alta directa sin consulta previa vía `Cliente::crearDirecto()`).
- Credenciales propias (`usuario`, `password`) generadas automáticamente al crearse, con contraseña temporal mostrada una sola vez.
- `hasMany` [[Proceso]] — un cliente puede tener varios casos.

## [[Proceso]] (el "Caso" legal)

Modelo `App\Models\Proceso`, tabla `procesos`. Equivale al "Caso" de `CLAUDE.md`.

- `cliente_id` → [[Cliente Ejecutivo]] (obligatorio).
- `materia_legal` (enum `CategoriaLegal`), `tipo_proceso` (texto libre, filtrado por [[Delito]] según la materia), `tiempo_proceso_meses`.
- `estado` (enum `EstadoProceso`): Activo, Cerrado, Archivado.
- `abogado_id` → [[Personal]] responsable del caso.
- `hasOne` [[Finanza]], `hasMany` [[Agenda]], `hasMany` `ProcesoDocumento`, `hasMany` `DocumentoSolicitud`.
- `Proceso::timeline()` arma una línea de tiempo unificada (consulta → conversión a cliente → proceso → agendas → documentos → cuotas) para el portal de Personal (`VerCaso`).

## [[Finanza]] y [[PlanPago]]

Aspecto financiero de un caso. Modelos `App\Models\Finanza` (tabla `finanzas`) y `App\Models\PlanPago` (tabla `plan_pagos`).

- `Finanza` — `hasOne` de [[Proceso]]: `costo`, `tipo_pago` (enum `TipoPago`: Semanal/Mensual/AlContado), `anticipo` y fecha de registro del anticipo.
- `Finanza::generarPlanPagos(cuotas, fechaInicio)` — genera las cuotas (`PlanPago`), respetando un máximo de cuotas calculado según `tiempo_proceso_meses` del proceso; bloquea la regeneración si ya hay cuotas pagadas.
- `PlanPago` — cada cuota: `fecha`, `monto`, `estado` (enum `EstadoPago`: Pendiente, PendienteConfirmación, Pagado, Vencido), `qr_path`, `comprobante`, `pagado_en`.
- `PlanPago::generarQr()` — genera y cachea un QR de referencia de pago (paquete `endroid/qr-code`).
- `PlanPago::registrarPagoCliente()` / `confirmarPago()` — ciclo de subida de comprobante por el cliente y confirmación manual por el staff, con notificaciones Filament en ambos sentidos.

## [[Recibo]] (NET-002, agregado 2026-08-11)

Comprobante formal de un pago, correlativo y verificable por QR. Modelo
`App\Models\Recibo`, tabla `recibos`, más tabla auxiliar
`recibo_correlativos` (fila única con el contador).

- Se emite a partir de una cuota pagada (`Recibo::emitirParaCuota()`,
  ligado 1:1 a un [[PlanPago]]) o de un anticipo confirmado
  (`Recibo::emitirParaAnticipo()`, ligado 1:1 a una [[Finanza]]) — nunca
  manualmente.
- `numero` correlativo (`REC-{año}-{6 dígitos}`), asignado con
  `lockForUpdate()` sobre `recibo_correlativos` dentro de una transacción
  corta — único y no reutilizable, sin garantía de ausencia de huecos.
- `identificador` (UUID) — es el valor expuesto en la URL pública de
  verificación, no el `id`.
- `hash_verificacion` — HMAC-SHA256 (no firma asimétrica) sobre
  número+monto+fecha+cliente+concepto+estado, con `config('app.key')`
  como clave. `Recibo::esAutentico()` lo recalcula y compara.
- `estado` (enum `EstadoRecibo`): Emitido, Anulado. `Recibo::anular()`
  conserva el `numero` original, registra motivo/autor/fecha, y
  **recalcula el hash** (el estado forma parte de los datos firmados).
- QR generado con `endroid/qr-code`, apunta a una URL firmada de Laravel
  (`URL::signedRoute`), no a datos estáticos.
- Verificación pública vía `ReciboVerificacionController`
  (`/verificar/recibo/{identificador}`), **sin autenticación** a
  propósito (se llega escaneando el QR impreso) — protegida por firma de
  URL + `throttle:30,1`. Vista de solo lectura.
- `ReciboResource` (panel Admin) — listado, sin páginas Create/Edit.

## Documentos: `ProcesoDocumento` y `DocumentoSolicitud`

- **`ProcesoDocumento`** (tabla `proceso_documentos`) — un archivo subido a un [[Proceso]], con `categoria` (enum `CategoriaDocumento`: Contrato, Poder, Memorial, Resolución, Demanda, Fotografía, Prueba, Otro) y `origen` (enum `OrigenDocumento`: Staff/Cliente). Si lo sube el cliente, notifica a todos los `User` admin.
- **`DocumentoSolicitud`** (tabla `documento_solicitudes`) — una solicitud de documento pendiente hecha por [[Personal]] a un [[Cliente Ejecutivo]] a través de su [[Proceso]]. `estado` (enum `EstadoSolicitudDocumento`: Pendiente/Cumplida). Al crearse, notifica al cliente.

## [[Personal]]

El equipo interno del despacho. Modelo `App\Models\Personal` (`Authenticatable`, guard `personal`), tabla `personal`.

- `rol` (enum `RolPersonal`): Administrador, Abogado, Asistente, Secretaria, Contador, Psicólogo, Trabajador Social.
- `especialidad_abogado` (enum `EspecialidadAbogado`) — usado para filtrar qué abogados pueden tomar un caso de una materia legal determinada. **Obligatorio cuando `rol = Abogado`** (validación de formulario en `PersonalResource`, sin `NOT NULL` a nivel de base de datos), opcional/oculto para los demás roles. Corregido en [[NET-001 - Especialidad abogado obligatoria]] (2026-08-10) — antes era opcional para cualquier rol, lo que permitía crear abogados sin especialidad y bloqueaba silenciosamente el wizard de [[Cliente Ejecutivo]].
- `estado` (enum `EstadoPersonal`: Activo/Inactivo/Suspendido) — controla el acceso al portal (`canAccessPanel`).
- Credenciales: `usuario` se genera automáticamente desde el teléfono; `must_change_password` fuerza cambio de contraseña en el primer ingreso.
- `hasMany` [[Agenda]] (como responsable), `hasMany` [[Proceso]] (como abogado).

## [[Delito]]

Catálogo de tipos de proceso/delito por materia legal. Modelo `App\Models\Delito`, tabla `delitos` (sin timestamps), columnas `materia_legal_id` (FK, NET-004) + `delito`. Se usa como select dependiente filtrado por `materia_legal_id` en los formularios de [[Consulta]] y [[Proceso]] (`Delito::opcionesPara()`).

## [[MateriaLegal]], [[Origen]], [[Departamento]] (NET-004, agregado 2026-09-02)

Catálogos administrables desde Filament (panel Admin, grupo "Catálogos"), reemplazando los antiguos enums `CategoriaLegal`/`OrigenConsulta` y las constantes duplicadas `Personal::CIUDADES`/`Consulta::CIUDADES` (ya eliminados).

- **`MateriaLegal`** (tabla `materias_legales`): `nombre`, `slug`, `activo`, `orden`. Sembrada con 12 valores — unión de lo que antes eran `CategoriaLegal` (7) y `EspecialidadAbogado` (12) — para que un abogado con cualquiera de las 5 especialidades que `CategoriaLegal` no cubría (Constitucional, Ambiental, Minero, Migratorio, Propiedad Intelectual) ya pueda recibir casos de esa materia. Referenciada por [[Consulta]] (`materia_legal_id`), [[Proceso]] (`materia_legal_id`) y [[Delito]] (`materia_legal_id`).
- **`Origen`** (tabla `origenes`): reemplaza `OrigenConsulta`. Referenciada por [[Consulta]] (`origen_id`).
- **`Departamento`** (tabla `departamentos`): los 9 departamentos de Bolivia (el campo se llamaba "ciudad" pero en realidad listaba departamentos — inconsistencia preexistente, corregida en el label). Referenciada por [[Consulta]] (`departamento_id`) y [[Personal]] (`departamento_id`).

`EspecialidadAbogado` (enum de [[Personal]]) se mantiene sin cambios — las comparaciones `especialidad_abogado` vs. materia legal seleccionada (en `ProcesoResource`, `MiAgenda`, `CreaClienteEjecutivoDirecto`) ahora resuelven el `slug` de la `MateriaLegal` elegida en vez de comparar directamente contra un string de enum. Ver [[NET-004 - Catálogos administrables]].

`RolPersonal` (Cargo de [[Personal]]) **no** se migró a catálogo — tiene acoplamiento de comportamiento (`Personal::esAbogado()`, reglas de derivación en `MiAgenda`) que requiere un ticket propio; ver detalle en [[NET-004 - Catálogos administrables]].

## Contraste con `CLAUDE.md`

`CLAUDE.md` lista como dominios: *Persona, Consulta, Ticket, Asignación, Cita, Cliente Ejecutivo, Caso, Historial*. En el código real:

| Dominio en CLAUDE.md | Equivalente real |
|---|---|
| Persona | No existe como entidad única — se divide en `User`, [[Personal]] y [[Cliente Ejecutivo]], cada uno `Authenticatable` con su propio guard |
| Consulta | [[Consulta]] — coincide |
| Ticket | **No existe.** Su función parece cubierta por `Consulta.estado` |
| Asignación | **No existe como entidad.** Se resuelve con campos (`Agenda.responsable_id`, `Proceso.abogado_id`, `Consulta.atendido_por`) |
| Cita | [[Agenda]] — generalizado también a Llamada y Reunión |
| Cliente Ejecutivo | [[Cliente Ejecutivo]] — coincide |
| Caso | [[Proceso]] — coincide (nombre distinto) |
| Historial | Parcial: existe [[AgendaHistorial]] (auditoría solo de Agenda), y `Proceso::timeline()` (línea de tiempo agregada), pero no hay un "Historial" como entidad transversal a todo el sistema |

## Ver también

- [[Arquitectura General]]
- [[Procesos]]
- [[Modelo Relacional]]
- [[NET-002 - Recibo de pago correlativo y QR verificable]]
- [[NET-004 - Catálogos administrables]]
- [[Fusión Visión-Actual]] — mapeo contra las entidades del sistema legado (`VISION_Y_REQUERIMIENTOS_NETLEY_FILAMENT.md`) que todavía no existen aquí (Contacto, Instancia/Seguimiento, InformeCierre, Comentario, Publicación, Postulación)
