# Front Page Legacy (netley.site) — Análisis para reconstrucción

> Extraído inspeccionando `https://netley.site` en vivo (HTML renderizado, red y scripts) el 2026-09-22. El sitio es el frontend público de captación de consultas del sistema anterior — no forma parte del repo `netley-laravel`. Este documento es la base para construir su equivalente moderno como front page pública de esta app.

## 1. Qué es y a quién sirve

Landing pública de captación de leads legales: cualquier persona escribe su duda jurídica de forma anónima, sin login, y un abogado responde en un plazo declarado de 3 días (por email o llamada). Es el punto de entrada del embudo `Consulta → Cliente Ejecutivo` que ya existe en el nuevo backend (`app/Http/Controllers/Admin/ConsultaController.php`, `Cliente::convertirDesdeConsulta()`).

Dato curioso: el `<meta property="og:url">` de esta página apunta a `https://www.netley.bo/` — es decir, este front page estaba pensado originalmente para vivir en el dominio que ahora estamos configurando.

## 2. Stack técnico del sitio legacy (para no repetir, referencia only)

- **Backend:** CodeIgniter (cookie `ci_session` en las cabeceras HTTP), no Laravel.
- **Tema:** AdminLTE 2 + Bootstrap 3/4 mezclados, jQuery 3.2/3.4, DataTables, iCheck, Toastr, Font Awesome 5, Google Fonts (Source Sans Pro).
- **CDN/Proxy:** ya está detrás de Cloudflare (`server: cloudflare`).
- **Analítica:** Google Analytics (GA universal + GA4) y Facebook SDK.
- **Problema de seguridad visible:** la IP de origen (`200.105.158.88`) se imprime directamente en el HTML de la página — anula parte de la protección de Cloudflare porque expone el servidor real.

## 3. Estructura de la página (de arriba hacia abajo)

1. **Navbar fija** (`navbar-fixed`), con estos enlaces, en este orden:
   - Inicio
   - Quiénes Somos
   - Consultas Gratuitas
   - **Banco de Consultas** — con badge "NUEVO"
   - Noticias y Publicaciones
   - Únete a Netley (postulación de profesionales)
   - Contactos
   - Sesión Admin (login, visible en el propio home público)
   - E-mail (enlace externo a webmail del hosting)
   - Usuario (botón de login de cliente, destacado con borde azul)

2. **Widget de chat flotante** (esquina, ícono 💬): al abrir muestra un textarea de solo lectura ("Vea respuestas similares...") y un botón "Banco de consultas" que simplemente redirige a `/Consultas/lista2cols`. No es un chatbot real, es un atajo de navegación disfrazado de chat.

3. **Caja "Consulta Online — Pagos QR"** (clic abre modal): modal con dos logos (banco + Netley) y una imagen de QR de pago para donaciones/pagos. No forma parte del flujo de captación de la consulta, es aparte.

4. **Contador de visitas**: `Fecha: {hoy} · Visitas: N` — contador simple server-side, poco relevante para el rediseño.

5. **Hero / bloque principal**: título "TE ESCUCHAMOS Y ORIENTAMOS ONLINE SIN COSTO ALGUNO" + párrafo de propuesta de valor + fondo con imagen (`imgs/fondos/8.jpg`).

6. **Formulario "CONSULTA GRATUITA"** — ver sección 4, es la pieza central de la página.

7. **Carrusel "Opinión de nuestros clientes"**: tabla con testimonios (texto + nombre opcional + calificación en estrellas), paginada con DataTables **en el cliente** (`pageLength: 7`, sin `searching`/`info`/`ordering`). El dataset completo (cientos de filas) se manda en el HTML inicial y JS solo pagina — nada de esto es lazy/AJAX. Con 261 páginas visibles esto es ineficiente: para la versión nueva, paginar en el servidor.

8. **"ÚLTIMAS CONSULTAS"**: carrusel de las consultas más recientes ya resueltas, cada una con enlace "Leer más..." a `/Consultas/verconsultasolo/{id}`. Lógica (ver sección 5): rota 4 tarjetas a la vez cada 9 segundos con fade.

9. **"PUBLICACIONES"**: sección de noticias (vacía al momento del análisis).

10. **Footer**: dirección de la oficina (actualmente comentada/oculta: *"NETLEY & ASOCIADOS — Edif. Michel, Piso 6 Of. 601 — La Paz, Bolivia"*), "Copyright Ctrl©2021" (el pie no se actualiza desde 2021), e íconos enlazando a Facebook, Twitter/X, YouTube y WhatsApp (`https://api.whatsapp.com/send?phone=59171536460&text=...`).

## 4. El formulario de consulta — campos exactos

`POST` a `/consultagController/guardar` (equivalente lógico al `ConsultaController@store` del nuevo backend).

| Campo (name) | Tipo/validación HTML | Obligatorio | Notas |
|---|---|---|---|
| `consulta` | textarea, `maxlength=1500`, patrón letras/números/puntuación básica | Sí | Placeholder pide **no incluir datos personales** dentro del texto |
| `nombres` | texto, 2–30, solo letras (con un espacio opcional) | Sí | Se fuerza a mayúsculas visualmente (`text-uppercase`) |
| `paterno` | texto, máx 30, solo letras | No | |
| `materno` | texto, máx 30, solo letras | No | |
| `email` | email, patrón básico | Sí | |
| `celular` | tel, patrón `[0-9]{8,11}`, bloquea teclas no numéricas en `onkeypress` | Sí | |
| `watss` (WhatsApp) | tel, mismo patrón que celular | Sí | |
| `captcha` | texto | Sí | Ver vulnerabilidad abajo |
| `captchaword` | hidden | — | Valor esperado del captcha, **visible en el DOM** |
| `pais`, `ciudad` | hidden, precargados `"Desconocido"` | — | Aparentemente pensados para geolocalización por IP, pero no encontré ningún script que los complete — la validación JS exige `pais === "BOLIVIA"` y con el valor por defecto nunca se cumple; posible bug legacy, no replicar tal cual |
| `estado` | hidden, `"Pendiente"` | — | Estado inicial de la consulta |
| `idabogado` | hidden, `"0"` | — | Sin abogado asignado |
| `tipo` | hidden, `"Gratuita"` | — | Todas las consultas del formulario público son gratuitas |
| `zona`, `especialidad`, `titulo` | hidden, vacíos | — | Sin uso aparente, probablemente residuales de una plantilla compartida con otro formulario |
| `fechaconsultag`, `ultimofyh`, `plazo`, `fechavisto`, `fechaestimada`, `fechaiguala` | hidden, todos precargados con la fecha de hoy | — | Seguimiento de SLA: fecha de creación, último contacto, fecha límite (promesa de 3 días), fecha de vista, fecha estimada de respuesta, fecha de "igualada" (¿cierre?). Útil como idea para el nuevo modelo de Consulta, pero **deben calcularse en el servidor**, no confiar en lo que manda el navegador |

**Validación cliente (`validateForm()`, JS puro):**
1. Rechaza la consulta si contiene un enlace (`https?://` o `www.`).
2. Exige que el texto de la consulta use solo caracteres "español básico" (letras con tildes/ñ, números, puntuación común).
3. Exige `pais === "BOLIVIA"` (roto, ver arriba).
4. Celular y WhatsApp: regex `^[1-9][0-9]{7,10}$`.
5. Email: regex genérico.
6. **Compara `captchaword` (el hidden) contra `captcha` (lo que el usuario escribió) directamente en JavaScript.**

**Vulnerabilidad a NO replicar — captcha:** el valor correcto del captcha viaja en un `<input type="hidden" name="captchaword" value="731">` visible en el propio HTML, y la validación es 100% client-side. Cualquiera puede leer el DOM (o desactivar JS) y pasar el captcha sin resolverlo. Para la versión nueva: generar el captcha en sesión de servidor (o usar algo como hCaptcha/Turnstile de Cloudflare, que ya usan en `netley.site` como challenge, o un captcha matemático validado en el backend de Laravel) y **nunca** mandar la respuesta esperada al navegador.

**Otro riesgo a evitar:** varios de los hidden (`idabogado`, `estado`, `tipo`, las fechas) son valores que el navegador manda y que, en teoría, el backend podría confiar ciegamente — eso permitiría a alguien manipular el POST (con devtools o `curl`) y, por ejemplo, autoasignarse a un abogado específico o marcar la consulta con un estado distinto. En el nuevo controlador estos valores deben fijarse siempre en servidor, no leerse del request.

## 5. Comportamientos JS relevantes para replicar (la parte buena)

- **Carrusel de últimas consultas** (sección "ÚLTIMAS CONSULTAS"): muestra 4 tarjetas `.consulta-item` a la vez; cada 9 segundos hace fade-out de todas, espera 100ms, y va mostrando las siguientes 4 con un `setTimeout` escalonado (150ms entre cada una) y fade-in de 600ms. El índice avanza de 4 en 4 y da la vuelta (`% totalConsultas`) — es un carrusel infinito simple, sin librería, ~40 líneas de jQuery. Reproducible fácil con CSS transitions + `setInterval` en el front nuevo (sin necesidad de jQuery).
- **Botón flotante de chat**: es solo un atajo visual a `/Consultas/lista2cols` (Banco de Consultas), no un chat real. Se puede omitir o reemplazarlo por un enlace directo sin fingir que es un chatbot.
- **Modal de pago QR**: caja separada del formulario de consulta, para donaciones/pagos, con dos logos y una imagen QR grande.

## 6. Mapeo de campos legacy → modelo `Consulta` actual

El nuevo modelo (`database/migrations/2026_09_15_000006_create_consultas_table.php`) ya cubre casi todo lo necesario:

| Legacy | Nuevo (`consultas`) |
|---|---|
| `nombres` | `nombre` |
| `paterno` | `apellido_paterno` |
| `materno` | `apellido_materno` |
| `celular` | `telefono` |
| `watss` | `whatsapp` |
| `email` | `email` |
| `consulta` (texto) | `descripcion` |
| `estado` (hidden) | `estado` (ya existe, con enum `EstadoConsulta`) |
| `pais`/`ciudad` (rotos) | `departamento_id`, `provincia`, `pais` (ya existen, mejor resueltos: el nuevo esquema usa un catálogo de `Departamento` en vez de texto libre) |
| — (no existía) | `origen_id` / `origen_otro` — de dónde vino el lead (el legacy no lo registraba) |
| — (no existía) | `materia_legal_id` — el formulario legacy no pedía la materia, iba todo a una bandeja única |
| `idabogado` | `atendido_por` |
| `tipo` (siempre "Gratuita") | No existe un campo equivalente — como todo el flujo público es gratuito, probablemente no hace falta |
| `plazo`, `fechaestimada`, etc. | No existen aún — si se quiere SLA visible (plazo de respuesta) habría que agregar una columna, calculada en el servidor al crear la consulta |
| `captchaword`/`captcha` | No es un campo de negocio — resolver con validación de servidor, no persistir |

## 7. Recomendaciones para el rediseño

1. **Captcha:** usar Cloudflare Turnstile (gratis, ya están en Cloudflare) o un captcha matemático simple validado en sesión de Laravel — nunca mandar la respuesta esperada al cliente.
2. **Estado/asignación de la consulta:** fijarlos siempre en el backend; ignorar cualquier valor que llegue del formulario para esos campos.
3. **Banco de Consultas:** paginar en servidor (Laravel ya tiene `paginate()` disponible, como se usa en `ClienteController@index`), no mandar cientos de filas y paginar en el navegador.
4. **Carrusel de últimas consultas:** buena idea de UX, reproducible sin jQuery con CSS/`IntersectionObserver` o una librería ligera.
5. **Datos de contacto:** conservar WhatsApp `71536460` y el enlace directo `https://api.whatsapp.com/send?phone=59171536460&text=...` si sigue siendo el número vigente — confirmar con el cliente antes de reutilizarlo.
6. **No repetir:** el widget de "chat" falso, la IP de origen impresa en el HTML, el link "Sesión Admin" destacado en el home público (mejor un login discreto o en subdominio aparte), ni la combinación de librerías duplicadas (dos versiones de jQuery, Bootstrap 3 y 4 a la vez) que tiene el sitio legacy.
