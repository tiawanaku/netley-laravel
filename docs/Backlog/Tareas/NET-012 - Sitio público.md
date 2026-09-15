# NET-012 — Sitio público

## Estado
**Hecho, 2026-09-03 (Hito 6).** Ver "Resultado".

## Objetivo
Construir el sitio público real: Home con las 4 áreas de práctica, formulario "Consulta Gratuita" (con protección anti-spam), "Únete a Netley" (postulación laboral), Publicaciones/artículos, Testimonios. Corresponde a la Épica 13 de `VISION_Y_REQUERIMIENTOS_NETLEY_FILAMENT.md`.

## Épica origen
13 — Sitio público (doc viejo).

## Estado actual verificado
`routes/web.php` solo define `/` devolviendo `welcome.blade.php` (el placeholder por defecto de Laravel) — **no hay sitio público real todavía**, ni siquiera el formulario de consulta gratuita.

## Entidades nuevas/afectadas
- El formulario de Consulta Gratuita escribe directamente en [[Consulta]] (ya existe el modelo, solo falta la vista pública + protección anti-bot — el doc viejo usaba un captcha matemático simple).
- Nuevo modelo `Publicacion` (tabla `publicaciones`): `titulo`, `categoria`, `resumen`, `cuerpo`, `fecha`.
- Nuevo modelo `Postulacion` (tabla `postulaciones`): `nombre_completo`, `correo`, `telefono`, `area_especializacion`, `mensaje`. El doc viejo señala que en el sistema legado **no había bandeja interna visible** para revisarlas — no asumir que debe haberla en Netley sin confirmar con el cliente (§10.7).
- Testimonios: depende de NET-013 (Comentarios).

## Depende de
Nada bloqueante técnicamente, pero es la última etapa del roadmap por ser la única parte fuera de los 3 paneles Filament (construido aparte con Livewire/Blade, compartiendo la misma base de datos — recomendación explícita del doc viejo §Épica 13).

## Complejidad
M/L — varias páginas públicas nuevas + formularios con validación anti-spam.

## Preguntas abiertas heredadas
- ¿"Publicaciones" debe conectarse a un CMS real (editor de contenido) o basta un CRUD simple de artículos? (§10.6)
- ¿Las postulaciones de "Únete a Netley" deben integrarse al panel (bandeja de revisión) o siguen resolviéndose por correo externo? (§10.7)

## Resultado (implementado, 2026-09-03)
Construido con Controllers + Blade "tradicional" (no Livewire), tal como recomendaba el doc viejo — comparte la misma base de datos que los paneles Filament sin ser parte de ellos. Home (`/`) real reemplaza al `welcome.blade.php` de Laravel.

- **Áreas de práctica**: en vez de las "4 áreas" hardcodeadas del sistema viejo (Civil, Comercial, Familiar, Penal), la home muestra dinámicamente `MateriaLegal::where('activo', true)` — el catálogo real de NET-004 — para no reintroducir un hardcode que ese ticket ya eliminó.
- **Consulta Gratuita**: formulario en la home, escribe directamente en `Consulta` (`origen_id` = "Página Web" del catálogo NET-004, `estado` = Nueva). Anti-spam: captcha matemático simple en sesión (`¿cuánto es X + Y?`), igual que el sistema viejo — sin servicio externo. Validación de teléfono con la misma regex que ya usa `ConsultaResource`.
- **Únete a Netley**: página `/unete-a-netley`, modelo `Postulacion` nuevo. **Sin bandeja de revisión en el panel** — a propósito, el doc viejo (§10.7) deja eso sin confirmar; los envíos se persisten para no perderlos, pero no se construyó ningún Resource/UI de moderación sin esa decisión.
- **Publicaciones**: modelo `Publicacion` (slug autogenerado con manejo de colisión) + `PublicacionResource` (Admin, **CRUD simple**, no un CMS — interpretación mínima ante la pregunta sin confirmar §10.6) + listado/ficha pública (`/publicaciones`, `/publicaciones/{slug}`), solo publicadas y con fecha ya cumplida.
- **Testimonios**: sección en la home con los Comentarios aprobados (NET-013) + formulario para dejar una opinión nueva.
- **"Quiénes somos"/"Contacto"** del menú del sistema viejo **no se construyeron** — no están en las 4 HU de la Épica 13 del doc viejo (§13.1–13.4), no se agregó alcance no pedido.
- **Bug real encontrado y corregido**: `Publicacion` y `Postulacion` no declaraban `$table` — el pluralizador de Eloquent adivina mal esas palabras en español ("publicacions"/"postulacions" en vez de "publicaciones"/"postulaciones"), causando `SQLSTATE[42S02]: Base table or view not found` en cuanto se insertaba un registro. Mismo problema, además, en el *slug* de `PublicacionResource` (Filament también adivina "publicacions"), con el mismo fix ya usado antes en `OrigenResource` (`protected static ?string $slug = 'publicaciones'`).
- Tests: `tests/Feature/Net012SitioPublicoTest.php`.

## Ver también
- [[Fusión Visión-Actual]]
- [[Dominios]]
- [[NET-013 - Comentarios]]
