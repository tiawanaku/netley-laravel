# Procesos (flujos operativos) — Netley

> ⚠️ Esta nota documenta **flujos de trabajo** (workflows) implementados en el código, no la entidad [[Proceso]] (el "caso" legal) — esa está documentada en [[Dominios]]. El nombre coincide por la estructura de carpetas pedida para el vault.

## 1. Flujo de una Consulta hasta convertirse en Caso

Implementado principalmente en `ConsultaResource` (Admin: `app/Filament/Resources/Consultas/ConsultaResource.php`; Personal: `app/Filament/Personal/Resources/Consultas/ConsultaResource.php`, con lógica casi duplicada entre ambos — ver [[Backlog]]).

```
[[Consulta]] creada (estado: Nueva)
        │
        │  acción "Agendar cita"
        ▼
Se crea una [[Agenda]] (tipo=Cita, estado=Pendiente)
Consulta.estado → Agendada
        │
        │  acción "Dar respuesta" (solo visible si ya existe una Agenda tipo Cita)
        ▼
Se crea un [[Answer]] ligado a la Agenda más reciente
(guarda quién respondió: Personal o User autenticado)
        │
        │  acción "Convertir a Cliente Ejecutivo"
        ▼
Cliente::convertirDesdeConsulta()
  - Copia datos de contacto a un nuevo [[Cliente Ejecutivo]]
  - Genera usuario + contraseña temporal (mostrada una sola vez)
  - Consulta.estado → ClienteEjecutivo (ya no editable después de esto)
```

Reglas relevantes encontradas en el código:

- La acción "Convertir" solo está visible si la consulta no tiene ya un cliente asociado (`! $record->cliente()->exists()`).
- Una vez que `Consulta.estado === ClienteEjecutivo`, el campo `estado` del formulario queda deshabilitado (`ConsultaResource::form()`).
- El teléfono y WhatsApp de la consulta se validan con `regex:/^[2-7][0-9]{6,7}$/` (7-8 dígitos, empieza en 2-7 — formato de teléfono boliviano).
- En el panel de **Personal**, agendar cita tiene reglas adicionales que no existen en Admin: horario laboral fijo (`Agenda::reglaHorarioLaboral()` — lunes a viernes, 08:00–17:00) y bloques de 15 minutos (`minutesStep(15)`).

## 2. Generación del Informe de Consulta (agregado 2026-08-10)

`ConsultaInformeController` + `Consulta::respuestas()`:

1. El botón "Informe" en `ConsultaResource` (Admin) solo aparece si la consulta tiene al menos una respuesta registrada.
2. Abre `GET /consultas/{consulta}/informe` en pestaña nueva — vista HTML con datos de contacto, detalle de la consulta (tipo de proceso, origen, forma de ingreso, descripción) y todas las respuestas (quién tomó la cita, quién respondió, fecha, contenido y **tiempo transcurrido entre el registro de la consulta y la respuesta** — pensado como métrica futura).
3. Desde ahí: botón "Imprimir" (`window.print()` con CSS `@media print`) y botón "Descargar PDF" (`GET /consultas/{consulta}/informe/pdf`, generado con DomPDF usando la misma plantilla Blade).

## 3. Alta directa de Cliente Ejecutivo (sin Consulta previa)

`CreaClienteEjecutivoDirecto` (trait en `app/Filament/Concerns/`) + `Cliente::crearDirecto()`. Usado por el wizard de creación de Cliente en el panel Admin (`ClienteResource`), en 3 pasos:

1. **Datos del cliente** — nombre, apellidos, CI, teléfono, WhatsApp.
2. **Datos del proceso** — materia legal, tipo de proceso/delito (filtrado por [[Delito]] según la materia), abogado asignado (**filtrado por `especialidad_abogado` == materia legal** — ver limitación en [[Backlog]]), tiempo del proceso en meses.
3. **Finanzas** — costo (iguala profesional), tipo de pago, anticipo, y opcionalmente genera el plan de pagos inmediatamente indicando número de cuotas y fecha de la primera.

Al completarse, se crea en una sola operación: [[Cliente Ejecutivo]] → [[Proceso]] → [[Finanza]] (y [[PlanPago]] si se indicaron cuotas), se genera el usuario/contraseña del portal cliente, y se descarga automáticamente el contrato de servicios en PDF.

## 4. Gestión documental de un Caso

- **Documentos del proceso** (`ProcesoDocumento`): se suben desde el panel Admin (`ProcesoResource` → RelationManager `DocumentosRelationManager`) o desde el Portal Cliente. Si el origen es `cliente`, se notifica a todos los `User` admin.
- **Solicitudes de documentos** (`DocumentoSolicitud`): el staff solicita un documento específico al cliente (`ProcesoResource` → RelationManager `SolicitudesRelationManager`); se notifica al cliente por Filament Notifications; el staff marca `marcarCumplida()` cuando el cliente lo sube.
- **Generador de Documentos** (`app/Filament/Pages/Documentos.php`): dado un Proceso, genera bajo demanda: Contrato de Servicios, Plan de Pagos, Aceptación del Cliente, Ficha del Cliente, Ficha del Proceso (todos PDF vía DomPDF).

## 5. Ciclo de pago de una cuota

1. Al generar el [[PlanPago]], cada cuota nace en estado `Pendiente`.
2. `PlanPago::generarQr()` genera (o reutiliza) un QR de referencia de pago.
3. El cliente sube su comprobante desde el Portal Cliente → `registrarPagoCliente()` → estado pasa a `PendienteConfirmación`, notifica a todos los `User` admin.
4. El staff/admin confirma manualmente → `confirmarPago()` → estado `Pagado`, notifica al cliente.
5. **(NET-002, 2026-08-11)** El propio `booted()` de `PlanPago` detecta el cambio a `Pagado` (`static::updated`) y llama automáticamente a `Recibo::emitirParaCuota()` si la cuota todavía no tiene [[Recibo]] — no es un paso manual aparte. Una vez emitido el recibo, `monto`/`fecha`/`estado` de esa cuota quedan bloqueados para edición y la cuota no puede eliminarse (excepciones lanzadas en `updating`/`deleting`).
6. El anticipo de una [[Finanza]] sigue el mismo patrón vía `Finanza::confirmarAnticipo()` → `Recibo::emitirParaAnticipo()`.
7. No se encontró en el código ningún job/comando automático que marque cuotas como `Vencido` — ese estado del enum `EstadoPago` existe pero no se le vio lógica que lo dispare automáticamente (ver [[Backlog]]).

## 6. Autenticación y acceso por portal

- **Personal**: usuario = teléfono (autogenerado), contraseña temporal en la primera creación, con `must_change_password` forzando cambio en el primer login (middleware `EnsurePersonalPasswordChanged` en `PersonalPanelProvider`). Reseteo posterior vía `Personal::generarAccesoPortal()` (acción "Restablecer contraseña" en el listado).
- **Cliente**: usuario/contraseña generados automáticamente al crearse (desde Consulta o alta directa), mostrados una única vez en la notificación de éxito.
- **User (Admin)**: autenticación estándar de Laravel/Filament, sin flujo especial documentado en el código más allá del login por defecto.

## Ver también

- [[Dominios]]
- [[Arquitectura General]]
- [[Modelo Relacional]]
- [[Backlog]]
- [[NET-002 - Recibo de pago correlativo y QR verificable]]
