# NET-001 — Especialidad abogado obligatoria

## Estado
Hecho (2026-08-10)

## Tipo
Bug / Regla de negocio

## Origen
Backlog técnico — Netley

## Problema

`especialidad_abogado` no es obligatorio en el formulario de Personal,
pero funcionalmente es requerido para la asignación de casos.

El wizard de creación de Cliente Ejecutivo filtra abogados mediante:

`especialidad_abogado == materia_legal`

Cuando no existe un abogado con la especialidad correspondiente,
el campo "Asignar caso a" queda vacío y el wizard queda bloqueado
sin explicar el motivo.

## Comportamiento esperado

`especialidad_abogado` es obligatorio en [[Personal]] cuando `rol = Abogado`,
tanto al crear como al editar, en el panel Admin (único lugar donde se
gestiona Personal). Para los demás roles el campo permanece oculto y
opcional, sin cambios. No se agregó restricción `NOT NULL` a nivel de
base de datos — la obligatoriedad es solo a nivel de formulario/Filament.
La lógica de asignación de abogados en el wizard de Cliente Ejecutivo
(`CreaClienteEjecutivoDirecto`) no se modificó.

## Criterios de aceptación

- [x] Crear un Personal con `rol = Abogado` sin especialidad bloquea el guardado con un mensaje claro.
- [x] El mismo bloqueo aplica al editar un Abogado existente y borrar su especialidad.
- [x] Crear/editar Personal con cualquier otro rol no exige ni muestra el campo.
- [x] El wizard de Cliente Ejecutivo (Admin y Personal) sigue asignando abogados correctamente tras el cambio.
- [x] No se modificó ningún otro archivo (`ProcesoResource`, `MiAgenda`, migraciones) — alcance respetado.

## Mockup

No aplica.

## Archivos relacionados

- `app/Filament/Resources/Personals/PersonalResource.php` (modificado)
- [[Cliente Ejecutivo]] — wizard `CreaClienteEjecutivoDirecto` (revisado, sin cambios)
- `app/Filament/Resources/Procesos/ProcesoResource.php` — mismo patrón de filtro, fuera de alcance (no modificado)
- `app/Filament/Personal/Pages/MiAgenda.php` — mismo patrón de filtro, fuera de alcance (no modificado)

## Documentación relacionada

- [[Dominios]]
- [[Arquitectura General]]
- [[Backlog]]

## Notas de Claude

**Diagnóstico:** el campo existía en el formulario (`PersonalResource.php:147-152`)
pero sin `->required()`. Tres lugares distintos del código filtran abogados
por `especialidad_abogado == materia_legal` (el wizard de Cliente Ejecutivo,
compartido por Admin y Personal vía `CreaClienteEjecutivoDirecto`;
`ProcesoResource`; y `MiAgenda`), pero solo el wizard y `MiAgenda` marcan
ese select como obligatorio — por eso solo ahí se manifestaba el bloqueo
silencioso descrito en el problema.

**Causa raíz:** el dato de origen (`Personal.especialidad_abogado`) podía
quedar nulo para un Abogado. Los selects que filtran ya funcionaban
correctamente (no debían mostrar abogados sin la especialidad correcta);
el problema era que se podía crear un Abogado sin especialidad en primer
lugar.

**Hallazgo relacionado (fuera de alcance, no corregido):** el enum
`EspecialidadAbogado` tiene 5 valores que `CategoriaLegal` no tiene
(Constitucional, Ambiental, Minero, Migratorio, Propiedad Intelectual).
Un abogado con una de esas especialidades nunca podrá hacer match con
ninguna materia legal de Consulta/Proceso, incluso con este fix aplicado.
Candidato a un ticket separado.

**Implementación (único archivo modificado):**

```diff
 Select::make('especialidad_abogado')
     ->label('Especialidad de abogado')
     ->options(EspecialidadAbogado::class)
     ->native(false)
     ->visible(fn (Get $get): bool => $get('rol') === RolPersonal::Abogado)
+    ->required(fn (Get $get): bool => $get('rol') === RolPersonal::Abogado)
+    ->validationMessages([
+        'required' => 'La especialidad es obligatoria para el rol de Abogado.',
+    ])
     ->columnSpanFull(),
```

**Pruebas ejecutadas (todas en navegador, sobre el entorno de desarrollo):**

1. Crear Personal `rol=Abogado` sin especialidad → bloqueado con "La
   especialidad es obligatoria para el rol de Abogado." ✅
2. Completar la especialidad y reenviar → se crea normalmente (ID 16). ✅
3. Crear Personal `rol=Secretaria` sin especialidad → se crea sin
   problema, campo no exigido ni visible. ✅ (ID 17)
4. Editar el Abogado existente (ID 13), borrar su especialidad, guardar
   → mismo bloqueo. Se restauró la especialidad original ("Penal") antes
   de continuar. ✅
5. Regresión — wizard de Cliente Ejecutivo en **Admin**: con materia
   legal "Familiar", el abogado de prueba con esa especialidad aparece
   correctamente en "Asignar caso a". ✅
6. Regresión — mismo wizard desde el **panel de Personal** (`/staff`,
   login `admin.qa`): idéntico resultado, el abogado aparece igual. ✅

Sin errores en `storage/logs/laravel.log` ni en la consola del navegador
durante ninguna de las pruebas.

**Problemas encontrados durante la implementación:** ninguno. El cambio
se comportó exactamente como se planeó, sin efectos secundarios
inesperados.