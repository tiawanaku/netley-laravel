# Modelo Relacional — Netley

> Extraído directamente de `database/migrations/*.php` (29 migraciones al 2026-08-10, más `recibo_correlativos`/`recibos`/`add_anticipo_confirmado_en` del 2026-08-10/11 agregadas en la actualización del 2026-09-02). No incluye `cache`, `jobs` ni `notifications`, que son tablas estándar de Laravel/Filament sin lógica de dominio propia.

## Diagrama de relaciones (resumen)

```
users (admins) ──< agenda_historiales
users ──< agendas (created_by)

consultas ──1:1── clientes (consulta_id, nullable)
consultas ──1:N── agendas
consultas ──N:1── personal (atendido_por)

clientes ──1:N── procesos
clientes ──1:N── agendas
clientes ──1:N── proceso_documentos

procesos ──N:1── personal (abogado_id)
procesos ──1:1── finanzas
procesos ──1:N── agendas
procesos ──1:N── proceso_documentos
procesos ──1:N── documento_solicitudes

finanzas ──1:N── plan_pagos

agendas ──N:1── personal (responsable_id)
agendas ──1:N── answers
agendas ──1:N── agenda_historiales
agendas ──N:N── personal (agenda_participantes)

answers ──N:1── personal (personal_id, nullable)
answers ──N:1── users (user_id, nullable)

documento_solicitudes ──N:1── personal
documento_solicitudes ──1:N── proceso_documentos (solicitud_id)

delitos — catálogo independiente, sin FK (join lógico por texto: area = materia_legal)

plan_pagos ──1:1── recibos (plan_pago_id, nullable, unique)
finanzas ──1:1── recibos (finanza_id, nullable, unique)
clientes ──1:N── recibos
procesos ──1:N── recibos
personal ──1:N── recibos (registrado_por_personal_id / anulado_por_personal_id)
users ──1:N── recibos (registrado_por_user_id / anulado_por_user_id)
recibo_correlativos — fila única con el contador (`ultimo_numero`), sin FK
```

## Tablas de dominio

### `consultas` ← [[Consulta]]
| Columna | Tipo | Notas |
|---|---|---|
| nombre, apellido_paterno | string | apellido_paterno obligatorio |
| apellido_materno, ci | string, nullable | ci agregado 2026-07-31 |
| telefono | string | |
| whatsapp | string, nullable | agregado 2026-07-28 |
| ciudad, email | string, nullable email | |
| tipo_proceso | string (enum `CategoriaLegal`) | |
| descripcion | text, nullable | |
| origen | string (enum `OrigenConsulta`) | |
| forma_ingreso | string, nullable (enum `FormaIngreso`) | |
| colegio_otros | string, nullable | |
| estado | string (enum `EstadoConsulta`), default `nueva` | |
| pago_inicial_monto | decimal(10,2), nullable | agregado 2026-07-29 |
| pago_inicial_registrado_en | datetime, nullable | |
| atendido_por | FK → `personal`, nullable, `nullOnDelete` | |

### `clientes` ← [[Cliente Ejecutivo]]
| Columna | Tipo | Notas |
|---|---|---|
| consulta_id | FK → `consultas`, **nullable** (desde 2026-08-04), `restrictOnDelete` | originalmente único y obligatorio |
| nombre, apellidos | string | |
| ci | string, nullable | agregado 2026-07-31 |
| telefono | string | |
| whatsapp | string, nullable | |
| usuario | string, unique | |
| password, remember_token | string | Authenticatable |

### `procesos` ← [[Proceso]]
| Columna | Tipo | Notas |
|---|---|---|
| cliente_id | FK → `clientes`, `restrictOnDelete` | |
| materia_legal | string (enum `CategoriaLegal`) | |
| tipo_proceso | string | texto del proceso/delito específico |
| tiempo_proceso_meses | unsignedSmallInteger | |
| estado | string (enum `EstadoProceso`), default `activo` | agregado 2026-07-29 |
| abogado_id | FK → `personal`, nullable, `nullOnDelete` | agregado 2026-07-29 |

### `finanzas` ← [[Finanza]]
| Columna | Tipo | Notas |
|---|---|---|
| proceso_id | FK → `procesos`, **unique** (1:1), `restrictOnDelete` | |
| costo | decimal(10,2) | |
| tipo_pago | string (enum `TipoPago`) | |
| anticipo | decimal(10,2), default 0 | agregado 2026-07-30 |
| anticipo_registrado_en | datetime, nullable | |

### `plan_pagos` ← [[PlanPago]]
| Columna | Tipo | Notas |
|---|---|---|
| finanza_id | FK → `finanzas`, `cascadeOnDelete` | |
| fecha | date | |
| monto | decimal(10,2) | |
| estado | string (enum `EstadoPago`), default `pendiente` | |
| qr_path | string, nullable | agregado 2026-07-29 |
| comprobante | string, nullable | |
| pagado_en | datetime, nullable | |

### `agendas` ← [[Agenda]]
| Columna | Tipo | Notas |
|---|---|---|
| tipo | string (enum `TipoAgenda`), default `cita` | |
| estado | string (enum `EstadoAgenda`), default `pendiente` | |
| fecha_inicio, fecha_fin | dateTime | |
| asunto | string, nullable | |
| descripcion | text, nullable | |
| modalidad | string, nullable (enum `ModalidadAgenda`) | |
| ubicacion | string, nullable | valores libres, ver `Agenda::UBICACIONES` |
| duracion_minutos | unsignedSmallInteger, nullable | |
| resultado | string, nullable (enum `ResultadoLlamada`) | solo para tipo Llamada |
| proceso_id | FK → `procesos`, nullable, `nullOnDelete` | |
| consulta_id | FK → `consultas`, nullable, `nullOnDelete` | |
| cliente_id | FK → `clientes`, nullable, `nullOnDelete` | |
| responsable_id | FK → `personal`, nullable, `nullOnDelete` | |
| created_by | FK → `users`, **obligatorio**, `restrictOnDelete` | vuelto nullable a nivel de columna el 2026-07-30 pero sigue siendo requerido por el modelo/formulario |

### `agenda_historiales` ← [[AgendaHistorial]]
| Columna | Tipo | Notas |
|---|---|---|
| agenda_id | FK → `agendas`, `cascadeOnDelete` | |
| usuario_id | FK → `users`, nullable, `nullOnDelete` | solo admins, nunca Personal (ver comentario en el modelo) |
| accion | string | `creado` / `actualizado` |
| datos_anteriores, datos_nuevos | json, nullable | |

### `agenda_participantes` (pivote N:N Agenda↔Personal)
| Columna | Tipo | Notas |
|---|---|---|
| agenda_id | FK → `agendas`, `cascadeOnDelete` | |
| personal_id | FK → `personal`, `cascadeOnDelete` | |
| rol | string, nullable | |
| — | unique(agenda_id, personal_id) | |

### `answers` ← [[Answer]]
| Columna | Tipo | Notas |
|---|---|---|
| agenda_id | FK → `agendas`, `cascadeOnDelete` | |
| respuesta | text | |
| personal_id | FK → `personal`, nullable, `nullOnDelete` | agregado 2026-08-10 |
| user_id | FK → `users`, nullable, `nullOnDelete` | agregado 2026-08-10 |

### `personal` ← [[Personal]]
| Columna | Tipo | Notas |
|---|---|---|
| nombre, apellidos | string | |
| ci | string, unique | |
| genero | string (enum `Genero`) | |
| fecha_nacimiento | date | |
| nacionalidad | string, default `Boliviana` | |
| estado_civil | string (enum `EstadoCivil`) | |
| profesion | string, nullable | |
| telefono | string | |
| whatsapp | string, nullable | |
| email | string | |
| direccion | string, nullable | |
| ciudad | string | valores en `Personal::CIUDADES` |
| numero_contrato | string, nullable, unique | |
| estado | string (enum `EstadoPersonal`), default `activo` | |
| fecha_inicio | date | |
| rol | string (enum `RolPersonal`) | |
| especialidad_abogado | string, nullable (enum `EspecialidadAbogado`) | **no obligatorio** — ver [[Backlog]] |
| foto | string, nullable | |
| documentos | json, nullable | |
| nota | text, nullable | |
| usuario | string, nullable, unique | agregado 2026-07-29, autogenerado desde teléfono |
| password, remember_token | string, nullable | Authenticatable |
| must_change_password | boolean | agregado 2026-07-29 |

### `delitos` ← [[Delito]]
| Columna | Tipo | Notas |
|---|---|---|
| area | string(50) | debe coincidir con el valor de `CategoriaLegal` |
| delito | string(255) | |
| — | sin timestamps, sin FK, index(area, delito) | |

### `proceso_documentos` ← `ProcesoDocumento`
| Columna | Tipo | Notas |
|---|---|---|
| proceso_id | FK → `procesos`, `cascadeOnDelete` | |
| categoria | string, default `otro` (enum `CategoriaDocumento`) | |
| nombre, archivo | string | |
| origen | string, default `staff` (enum `OrigenDocumento`) | |
| personal_id | FK → `personal`, nullable, `nullOnDelete` | |
| cliente_id | FK → `clientes`, nullable, `nullOnDelete` | |
| solicitud_id | FK → `documento_solicitudes`, nullable, `nullOnDelete` | |

### `documento_solicitudes` ← `DocumentoSolicitud`
| Columna | Tipo | Notas |
|---|---|---|
| proceso_id | FK → `procesos`, `cascadeOnDelete` | |
| personal_id | FK → `personal`, `restrictOnDelete` | |
| descripcion | text | |
| estado | string, default `pendiente` (enum `EstadoSolicitudDocumento`) | |

### `recibos` ← [[Recibo]]
| Columna | Tipo | Notas |
|---|---|---|
| numero | string, unique | `REC-{año}-{6 dígitos}`, asignado vía `recibo_correlativos` |
| identificador | uuid, unique | expuesto en la URL pública de verificación |
| hash_verificacion | string(64) | HMAC-SHA256, ver [[Recibo]] |
| origen_tipo | string (enum `OrigenRecibo`) | Cuota / Anticipo |
| plan_pago_id | FK → `plan_pagos`, nullable, **unique**, `restrictOnDelete` | 1:1, presente solo si origen = Cuota |
| finanza_id | FK → `finanzas`, nullable, **unique**, `restrictOnDelete` | 1:1, presente solo si origen = Anticipo |
| cliente_id | FK → `clientes`, `restrictOnDelete` | |
| proceso_id | FK → `procesos`, `restrictOnDelete` | |
| concepto | string | |
| monto | decimal(10,2) | |
| moneda | string(3), default `BOB` | |
| fecha_pago | date | |
| estado | string, default `emitido` (enum `EstadoRecibo`) | Emitido / Anulado |
| registrado_por_personal_id / registrado_por_user_id | FK, nullable, `nullOnDelete` | par exclusivo, mismo patrón que [[Answer]] (ver ADR-005) |
| anulado_en, anulado_por_personal_id, anulado_por_user_id, motivo_anulacion | nullable | se completan solo al anular |

### `recibo_correlativos`
| Columna | Tipo | Notas |
|---|---|---|
| ultimo_numero | unsignedInteger, default 0 | fila única, actualizada con `lockForUpdate()` dentro de una transacción corta |

## Notas de evolución del esquema (relevantes)

- `proceso_mensajes` fue **creada y luego eliminada** (`2026_07_29_010004_create...` → `2026_07_31_193048_drop...`) — funcionalidad de mensajería entre cliente y staff que se implementó y se retiró. No queda modelo asociado.
- `clientes.consulta_id` pasó de obligatorio+único a **nullable** el 2026-08-04, habilitando altas directas de Cliente Ejecutivo sin pasar por una Consulta.
- `answers.personal_id` / `answers.user_id` agregados el 2026-08-10 (esta sesión) para poder atribuir cada respuesta a quien la escribió.
- `recibo_correlativos` y `recibos` creadas el 2026-08-10 (NET-002, commit `d0b4180` del 2026-08-11) — comprobante de pago correlativo y verificable por QR, ver [[Recibo]] y [[NET-002 - Recibo de pago correlativo y QR verificable]].
- `finanzas.anticipo_confirmado_en` agregada junto con lo anterior — marca cuándo se confirmó realmente el anticipo (distinto de cuándo se registró), dispara `Recibo::emitirParaAnticipo()`.

## Ver también

- [[Dominios]]
- [[Arquitectura General]]
- [[Procesos]]
