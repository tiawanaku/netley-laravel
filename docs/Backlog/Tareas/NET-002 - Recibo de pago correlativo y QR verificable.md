# NET-002 — Recibo de pago correlativo y QR verificable

## Estado

Implementado (2026-08-11, commit `d0b4180`). Esta nota se actualizó
2026-09-02 comparando el código real contra los criterios de aceptación
de abajo — el resto del documento (Objetivo/Problema/Comportamiento
esperado/etc.) se conserva como el pedido original.

## Solución técnica (verificada en el código)

- **Modelo** `App\Models\Recibo` (tabla `recibos`) + tabla auxiliar
  `recibo_correlativos` (fila única con `ultimo_numero`).
- **Correlativo**: `Recibo::siguienteNumero()` asigna el número dentro de
  una transacción corta con `lockForUpdate()` sobre la fila de
  `recibo_correlativos`, formato `REC-{año}-{6 dígitos}` (p. ej.
  `REC-2026-000001`). Resuelve concurrencia por bloqueo pesimista a nivel
  de fila; no garantiza ausencia de huecos, sí unicidad y no reuso.
- **Identificador único**: `identificador` (UUID), autogenerado en
  `booted()`. Es el valor expuesto en la URL de verificación, no el `id`
  autoincremental.
- **Verificación (no es firma digital, es HMAC)**: `hash_verificacion`
  = `hash_hmac('sha256', "{numero}|{monto}|{fecha_pago}|{cliente_id}|{concepto}|{estado}", config('app.key'))`.
  `Recibo::esAutentico()` recalcula el hash y compara con `hash_equals()`.
  Cualquier alteración de esos campos invalida la autenticidad.
- **QR**: apunta a una URL firmada de Laravel (`URL::signedRoute`), no a
  datos estáticos — `endroid/qr-code` solo dibuja el QR de esa URL.
- **Verificación pública**: `ReciboVerificacionController` en
  `routes/web.php`, rutas `recibos.verificar` y `recibos.verificar.pdf`,
  middleware `['signed', 'throttle:30,1']` — **sin autenticación** a
  propósito (se accede escaneando el QR impreso), protegida solo por la
  firma de la URL y throttling. Vista de solo lectura
  (`resources/views/recibos/recibo.blade.php`), no permite modificar el
  recibo.
- **Anulación**: `Recibo::anular($motivo)` — cambia `estado` a `Anulado`,
  registra `anulado_en`/`motivo_anulacion`/quién anula, y **recalcula el
  hash** (incluye `estado` en los datos firmados, así que un recibo
  anulado no puede volver a pasar como "emitido" ni viceversa). El
  `numero` original se conserva siempre — no hay borrado físico ni
  restricción de FK que lo permita (`restrictOnDelete` en `recibos` hacia
  `plan_pagos`/`finanzas`/`clientes`/`procesos`).
- **Origen del recibo** (enum `OrigenRecibo`): `Cuota` (vía
  `Recibo::emitirParaCuota()`, ligado a un `PlanPago` — FK única, un
  recibo por cuota) o `Anticipo` (vía `Recibo::emitirParaAnticipo()`,
  ligado a una `Finanza` — FK única, un recibo por anticipo).
- **Listado administrativo**: `app/Filament/Resources/Recibos/ReciboResource.php`
  (panel Admin, sin páginas Create/Edit — los recibos solo se emiten
  desde el flujo de pagos, nunca manualmente).
- **PDF**: `ReciboVerificacionController::pdf()`, DomPDF con formato
  angosto tipo ticket (288×700 pt).

## Criterios de aceptación — estado real

- [x] El recibo queda almacenado en Finanzas (tabla `recibos`, FK a
      `finanzas`/`plan_pagos`/`procesos`/`cliente`).
- [x] El administrador puede consultar un listado de recibos
      (`ReciboResource`).
- [ ] Búsqueda/filtro por número, cliente y período en el listado — no
      verificado en detalle sobre `ReciboResource`; revisar antes de
      cerrar el ítem por completo.
- [x] El administrador puede abrir el detalle y descargar/imprimir el
      recibo (misma vista `recibos.recibo`, HTML y PDF).
- [x] Un recibo no puede eliminarse sin perder trazabilidad — no hay
      acción de borrado, y las FK son `restrictOnDelete`.
- [x] Un recibo anulado conserva su número original.
- [x] Un número de recibo utilizado no puede reutilizarse (contador
      monotónico + `unique` en `numero`).
- [x] El recibo permanece vinculado al pago que lo originó (FK única
      `plan_pago_id`/`finanza_id`).

## Nota sobre esta actualización

No se verificó línea por línea el criterio de "búsqueda por número /
cliente / filtro por período" en `ReciboResource` — se deja marcado como
pendiente de confirmar en vez de asumirlo, siguiendo la regla de
`CLAUDE.md` de no dar por buena una implementación sin comprobarla.

## Tipo

Funcionalidad / Seguridad / Pagos

## Objetivo

Implementar un sistema formal de recibos para los pagos registrados
en Netley.

Cada pago confirmado debe generar un recibo único, correlativo y
verificable mediante QR.

El recibo debe quedar almacenado y relacionado con el pago dentro
del módulo de Finanzas, permitiendo al administrador consultar,
auditar, imprimir y verificar los recibos emitidos.

## Problema

Actualmente el registro de un pago no genera un comprobante con
un mecanismo de verificación que permita comprobar posteriormente
que el recibo fue emitido realmente por Netley y que sus datos
no fueron alterados.

## Comportamiento esperado

Al registrar un pago confirmado:

1. Generar automáticamente un número de recibo correlativo.
2. Generar un identificador único para el recibo.
3. Generar un QR asociado exclusivamente a ese recibo.
4. El QR debe permitir verificar la autenticidad del recibo.
5. La información verificada debe corresponder al pago almacenado
   en Netley.
6. Generar el comprobante/recibo correspondiente.
7. El recibo debe poder ser consultado posteriormente.
8. El número de recibo no debe poder duplicarse.

## Requisito de seguridad

El QR no debe contener únicamente información estática que pueda
ser copiada o modificada.

Debe existir un mecanismo mediante el cual Netley pueda comprobar
que el recibo:

- fue generado por el sistema;
- corresponde a un pago existente;
- no fue alterado;
- posee un identificador único.

La solución técnica debe ser propuesta después de analizar
la arquitectura existente.

## Correlativo

El número de recibo debe ser único y correlativo.

Debe analizarse:

- dónde almacenar el correlativo;
- cómo evitar duplicados;
- cómo manejar concurrencia;
- qué ocurre si dos usuarios registran pagos simultáneamente;
- si el correlativo debe ser global o por algún contexto.

No asumir la solución antes de revisar el modelo actual.

## Datos del recibo

Analizar qué información del pago debe aparecer en el recibo.

Como mínimo evaluar:

- número de recibo;
- fecha;
- cliente;
- concepto;
- importe;
- moneda;
- usuario/personal que registra el pago;
- identificador único;
- QR de verificación.

## Verificación

El QR debe permitir acceder a un mecanismo de verificación.

Ejemplo conceptual:

/verificar/recibo/{identificador}

La URL y el mecanismo definitivo deben ser determinados después
del análisis técnico.

La página de verificación no debe permitir modificar el recibo.

## Criterios de aceptación

- [ ] El recibo queda almacenado en Finanzas.
- [ ] El administrador puede consultar un listado de recibos.
- [ ] El listado permite búsqueda por número de recibo.
- [ ] El listado permite búsqueda por cliente.
- [ ] El listado permite filtrar por período.
- [ ] El administrador puede abrir el detalle del recibo.
- [ ] El administrador puede descargar/imprimir el recibo.
- [ ] Un recibo emitido no puede eliminarse de forma que se pierda
      la trazabilidad.
- [ ] Un recibo anulado conserva su número original.
- [ ] Un número de recibo utilizado no puede reutilizarse.
- [ ] El recibo permanece vinculado al pago que lo originó.

## Seguridad

No almacenar información sensible innecesaria dentro del QR.

Evaluar el uso de:

- identificador aleatorio/UUID;
- firma digital o HMAC;
- URL de verificación;
- hash/firma de los datos relevantes.

La solución definitiva debe ser determinada por el análisis
de arquitectura y seguridad.

## Documentación relacionada

- [[Dominios]]
- [[Arquitectura General]]
- [[Modelo Relacional]]
- [[Decisiones Arquitectónicas]]

## Mockup

Pendiente.

## Registro de recibos en Finanzas

Cada recibo generado a partir de un pago debe quedar almacenado
permanentemente como parte del módulo de Finanzas.

El administrador debe disponer de una sección para consultar
los recibos emitidos y realizar seguimiento de los pagos.

### Listado de recibos

Crear una vista administrativa de recibos con comportamiento
similar a un libro de contabilidad/auditoría.

El listado debe permitir como mínimo:

- Número de recibo.
- Fecha de emisión.
- Cliente.
- Caso/Proceso relacionado.
- Concepto del pago.
- Importe.
- Moneda.
- Método de pago, si existe en el sistema.
- Usuario/personal que registró el pago.
- Estado del pago.
- Estado del recibo.
- Identificador de verificación.

### Funciones administrativas

El administrador debe poder:

- Consultar el recibo.
- Ver el detalle completo del pago.
- Ver el recibo generado.
- Descargar/imprimir el recibo.
- Buscar por número de recibo.
- Buscar por cliente.
- Filtrar por fecha.
- Filtrar por estado.
- Filtrar por usuario que registró el pago.
- Consultar el comprobante mediante el QR.
- Verificar la autenticidad del recibo.

### Auditoría

El sistema debe conservar la trazabilidad del recibo.

No se debe permitir eliminar físicamente un recibo que ya haya
sido emitido si esto rompe la trazabilidad financiera.

Si existe una necesidad de anulación, debe analizarse un mecanismo
de "Anulado" en lugar de eliminar el registro.

Una anulación debe conservar:

- Número de recibo original.
- Fecha de emisión.
- Usuario que realizó la anulación.
- Fecha de anulación.
- Motivo de anulación.

El número de recibo anulado no debe volver a utilizarse.

### Integridad

El recibo debe permanecer vinculado al pago que lo originó.

La eliminación o modificación de un pago debe analizarse para evitar
que deje un recibo huérfano o que permita alterar un recibo ya emitido.

### Reportes

La solución debe evaluar la posibilidad de que el listado de Finanzas
permita posteriormente generar reportes por:

- período;
- cliente;
- usuario;
- método de pago;
- estado;
- concepto;
- monto total.

Esta funcionalidad puede implementarse inicialmente como listado y
filtros, dejando reportes avanzados para una tarea posterior si
incrementan demasiado el alcance.