# Arquitectura General — Netley

> Generado a partir de análisis directo del código fuente (no de suposiciones). Última revisión: 2026-08-10.

## Stack

- **Laravel 12**, PHP 8.2+
- **FilamentPHP 4.0**
- **MySQL**
- Paquetes clave: `barryvdh/laravel-dompdf` (PDFs), `endroid/qr-code` (QR de pagos), `saade/filament-full-calendar` (calendario)

## Idea central: tres paneles, tres guards, tres modelos autenticables

Netley no es una sola aplicación Filament — son **tres paneles independientes** montados sobre la misma base de datos, cada uno con su propio *guard* de autenticación y su propio modelo `Authenticatable`:

| Panel | Ruta | Guard | Modelo | Provider |
|---|---|---|---|---|
| Admin | `/admin` | `web` | `User` | `AdminPanelProvider` |
| Personal (staff) | `/staff` | `personal` | `Personal` | `PersonalPanelProvider` |
| Cliente (portal) | `/portal` | `clientes` | `Cliente` | `ClientePanelProvider` |

Cada modelo autenticable implementa `Filament\Models\Contracts\FilamentUser` y decide su propio acceso vía `canAccessPanel()`:

- `User::canAccessPanel()` no está restringido explícitamente (acceso abierto a `admin` para cualquier usuario autenticado con ese guard).
- `Personal::canAccessPanel()` exige `$panel->getId() === 'personal' && $this->estado === EstadoPersonal::Activo`.
- `Cliente::canAccessPanel()` exige `$panel->getId() === 'cliente'`.

Cada panel además descubre sus propios Resources/Pages/Widgets en subcarpetas separadas:

- Admin → `app/Filament/Resources`, `app/Filament/Pages`, `app/Filament/Widgets`
- Personal → `app/Filament/Personal/Resources`, `app/Filament/Personal/Pages`, `app/Filament/Personal/Widgets`
- Cliente → `app/Filament/Cliente/Resources` (vacío actualmente), `app/Filament/Cliente/Pages`

Ver detalle de dominios en [[Dominios]] y de tablas en [[Modelo Relacional]].

## Patrón de código: "fat model", sin capa de servicios

No existe una capa de Services/Repositories. La lógica de negocio vive en dos lugares:

1. **Métodos de modelo Eloquent**, por ejemplo:
   - `Cliente::convertirDesdeConsulta()` / `Cliente::crearDirecto()` — conversión de Consulta a Cliente Ejecutivo, generación de usuario/contraseña.
   - `Finanza::generarPlanPagos()` — cálculo y generación de cuotas.
   - `PlanPago::generarQr()`, `registrarPagoCliente()`, `confirmarPago()` — ciclo de vida de un pago.
   - `Personal::generarAccesoPortal()` — reseteo de credenciales del portal de staff.
   - `Proceso::timeline()` — línea de tiempo unificada del caso (agrega datos de Consulta, Cliente, Agenda, Documentos y Pagos).
   - `Recibo::emitirParaCuota()` / `emitirParaAnticipo()` / `anular()` — emisión con correlativo + hash HMAC de verificación, y anulación con recálculo de hash (NET-002).

2. **Closures dentro de Filament Actions**, dentro de los `*Resource::table()` (p. ej. `agendarCita`, `darRespuesta`, `convertir`, `informe` en `ConsultaResource`).

No hay controllers HTTP tradicionales dentro de los tres paneles Filament, con dos excepciones puntuales, ambas fuera de cualquier panel:

- `App\Http\Controllers\ConsultaInformeController` — vista imprimible y descarga en PDF del informe de una consulta (protegida por `auth`, guard `web`/Admin).
- `App\Http\Controllers\ReciboVerificacionController` (NET-002, agregado 2026-08-11) — verificación pública de un recibo de pago vía el QR impreso; **sin autenticación** a propósito, protegida por `URL::signedRoute` + `throttle:30,1`. Es la única ruta del sistema pensada para ser accedida por alguien sin cuenta en ninguno de los tres paneles.

## Enrutamiento

- Cada panel Filament registra sus propias rutas automáticamente (prefijo `admin`, `staff`, `portal`).
- `routes/web.php` contiene: la ruta raíz (`/`); las dos rutas del informe de consulta (`consultas/{consulta}/informe` y `.../informe/pdf`), protegidas con middleware `auth` (guard `web`, solo Admin); y las dos rutas públicas de verificación de recibo (`/verificar/recibo/{identificador}` y `.../pdf`), protegidas con `signed` + `throttle:30,1` en vez de autenticación.

## Generación de documentos

- **`app/Filament/Pages/Documentos.php`** — página "Generador de Documentos" en el panel Admin: dado un Proceso, permite descargar contrato de servicios, plan de pagos, aceptación del cliente, ficha del cliente y ficha del proceso (todos vía DomPDF).
- **`ConsultaInformeController`** — informe de una consulta con datos de contacto, tipo de proceso y respuestas dadas; soporta vista HTML imprimible y descarga PDF con la misma plantilla Blade (`resources/views/consultas/informe.blade.php`).

## Multi-tenencia de datos entre paneles

No hay separación de tenants: los tres paneles comparten las mismas tablas. La separación es puramente de **acceso** (guard + `canAccessPanel()` + queries filtradas), no de datos. Por ejemplo, `Personal\Resources\Consultas\ConsultaResource::getEloquentQuery()` filtra las consultas visibles para un miembro del staff según si tienen o no una cita ya asignada a él (`Agenda.responsable_id`).

## Contraste con `CLAUDE.md`

`CLAUDE.md` describe el flujo funcional como:

```
Visitante → Prospecto → Consulta Virtual → Ticket → Asignación → Cita → Cliente Ejecutivo → Caso → Historial
```

El código real no contiene modelos `Ticket` ni `Asignación`. La asignación de un caso a un profesional se resuelve mediante campos (`Agenda.responsable_id`, `Proceso.abogado_id`, `Consulta.atendido_por`), no mediante una entidad dedicada. Ver el detalle de esta discrepancia en [[Dominios]] y una entrada formal en [[Decisiones Arquitectónicas]].

## Ver también

- [[Dominios]]
- [[Procesos]]
- [[Modelo Relacional]]
- [[Backlog]]
- [[Decisiones Arquitectónicas]]
