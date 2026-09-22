<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Netley &amp; Asociados — Asesoramiento legal gratuito en Bolivia</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Envíe su consulta legal de forma gratuita y anónima. Un abogado de Netley &amp; Asociados le responde en un máximo de 3 días hábiles.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 antialiased">

    {{-- Nav --}}
    <header class="sticky top-0 z-40 bg-white/90 backdrop-blur border-b border-slate-200">
        <nav class="mx-auto max-w-6xl px-4 sm:px-6 flex items-center justify-between h-16">
            <a href="#inicio" class="font-bold text-lg text-slate-900">Netley <span class="text-blue-600">&amp; Asociados</span></a>

            <div class="hidden md:flex items-center gap-6 text-sm font-medium text-slate-600">
                <a href="#inicio" class="hover:text-blue-600">Inicio</a>
                <a href="#nosotros" class="hover:text-blue-600">Quiénes somos</a>
                <a href="#como-funciona" class="hover:text-blue-600">Cómo funciona</a>
                <a href="#consulta" class="hover:text-blue-600">Consulta gratuita</a>
                <a href="#contacto" class="hover:text-blue-600">Contacto</a>
            </div>

            <div class="flex items-center gap-4">
                <a href="#consulta" class="hidden sm:inline-block rounded-full bg-blue-600 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-700">
                    Hacer consulta
                </a>
                <details class="relative">
                    <summary class="list-none cursor-pointer text-xs text-slate-400 hover:text-slate-600 select-none">Acceso interno</summary>
                    <div class="absolute right-0 mt-2 w-40 rounded-lg border border-slate-200 bg-white shadow-lg text-sm overflow-hidden">
                        <a href="{{ route('admin.login') }}" class="block px-4 py-2 hover:bg-slate-50">Admin</a>
                        <a href="{{ route('staff.login') }}" class="block px-4 py-2 hover:bg-slate-50">Staff</a>
                        <a href="{{ route('portal.login') }}" class="block px-4 py-2 hover:bg-slate-50">Portal Cliente</a>
                    </div>
                </details>
            </div>
        </nav>
    </header>

    {{-- Hero --}}
    <section id="inicio" class="bg-gradient-to-b from-blue-50 to-slate-50">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 py-16 sm:py-24 text-center">
            <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-slate-900">
                Le escuchamos y orientamos<br class="hidden sm:block"> online, sin costo alguno
            </h1>
            <p class="mt-5 max-w-2xl mx-auto text-lg text-slate-600">
                Profesionales calificados analizan su caso y le brindan la asesoría jurídica que necesita,
                de una manera fácil y entendible. Su consulta es anónima y la respuesta llega por correo
                o WhatsApp en un máximo de 3 días hábiles.
            </p>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                <a href="#consulta" class="rounded-full bg-blue-600 px-6 py-3 font-semibold text-white hover:bg-blue-700">
                    Hacer mi consulta gratuita
                </a>
                <a href="https://api.whatsapp.com/send?phone=59171536460&text=Hola%2C%20deseo%20realizar%20una%20consulta%20Netley"
                   target="_blank" rel="noopener"
                   class="rounded-full border border-slate-300 bg-white px-6 py-3 font-semibold text-slate-700 hover:bg-slate-100">
                    Escribir por WhatsApp
                </a>
            </div>
        </div>
    </section>

    {{-- Cómo funciona --}}
    <section id="como-funciona" class="mx-auto max-w-6xl px-4 sm:px-6 py-16">
        <h2 class="text-2xl font-bold text-center text-slate-900">Cómo funciona</h2>
        <div class="mt-10 grid gap-8 sm:grid-cols-3">
            <div class="text-center">
                <div class="mx-auto h-12 w-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-lg">1</div>
                <h3 class="mt-4 font-semibold text-slate-900">Cuéntenos su caso</h3>
                <p class="mt-2 text-sm text-slate-600">Complete el formulario con su consulta. No incluya datos personales dentro del texto: hay campos aparte para eso.</p>
            </div>
            <div class="text-center">
                <div class="mx-auto h-12 w-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-lg">2</div>
                <h3 class="mt-4 font-semibold text-slate-900">Un abogado lo revisa</h3>
                <p class="mt-2 text-sm text-slate-600">Un asesor de la especialidad correspondiente analiza su situación.</p>
            </div>
            <div class="text-center">
                <div class="mx-auto h-12 w-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-lg">3</div>
                <h3 class="mt-4 font-semibold text-slate-900">Le respondemos</h3>
                <p class="mt-2 text-sm text-slate-600">Recibe la orientación por correo electrónico o WhatsApp, en un máximo de 3 días hábiles.</p>
            </div>
        </div>
    </section>

    {{-- Quiénes somos --}}
    <section id="nosotros" class="bg-white border-y border-slate-200">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 py-16">
            <h2 class="text-2xl font-bold text-slate-900">Quiénes somos</h2>
            <p class="mt-4 max-w-3xl text-slate-600">
                Netley &amp; Asociados es un equipo de abogados y asesores legales que ofrece orientación
                jurídica gratuita a personas en toda Bolivia, en las siguientes materias:
            </p>
            @if ($materiasLegales->isNotEmpty())
                <div class="mt-6 flex flex-wrap gap-2">
                    @foreach ($materiasLegales as $materia)
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-sm text-slate-700">{{ $materia->nombre }}</span>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Formulario de consulta --}}
    <section id="consulta" class="mx-auto max-w-2xl px-4 sm:px-6 py-16">
        <div class="rounded-2xl border border-slate-200 bg-white shadow-sm p-6 sm:p-8">
            <h2 class="text-2xl font-bold text-slate-900 text-center">Consulta gratuita</h2>
            <p class="mt-2 text-sm text-slate-500 text-center">
                Todos los campos marcados con (*) son obligatorios.
            </p>

            @if (session('status'))
                <div class="mt-6 rounded-lg bg-green-50 border border-green-200 text-green-800 text-sm p-4">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mt-6 rounded-lg bg-red-50 border border-red-200 text-red-800 text-sm p-4">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('consultas.store') }}" class="mt-6 space-y-5">
                @csrf

                <div>
                    <label for="descripcion" class="block text-sm font-medium text-slate-700">Su consulta (*)</label>
                    <textarea id="descripcion" name="descripcion" rows="4" maxlength="1500" required
                        placeholder="Describa su situación. No incluya nombres de terceros ni datos personales dentro del texto."
                        class="mt-1 w-full rounded-lg border border-slate-300 p-3 text-sm focus:border-blue-500 focus:ring-blue-500">{{ old('descripcion') }}</textarea>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="nombre" class="block text-sm font-medium text-slate-700">Nombre (*)</label>
                        <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" required maxlength="255"
                            class="mt-1 w-full rounded-lg border border-slate-300 p-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="apellido_paterno" class="block text-sm font-medium text-slate-700">Apellido paterno (*)</label>
                        <input type="text" id="apellido_paterno" name="apellido_paterno" value="{{ old('apellido_paterno') }}" required maxlength="255"
                            class="mt-1 w-full rounded-lg border border-slate-300 p-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="apellido_materno" class="block text-sm font-medium text-slate-700">Apellido materno</label>
                        <input type="text" id="apellido_materno" name="apellido_materno" value="{{ old('apellido_materno') }}" maxlength="255"
                            class="mt-1 w-full rounded-lg border border-slate-300 p-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-700">E-mail</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" maxlength="255"
                            class="mt-1 w-full rounded-lg border border-slate-300 p-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="telefono" class="block text-sm font-medium text-slate-700">Celular (*)</label>
                        <input type="tel" id="telefono" name="telefono" value="{{ old('telefono') }}" required
                            placeholder="70000000"
                            class="mt-1 w-full rounded-lg border border-slate-300 p-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <label for="whatsapp" class="block text-sm font-medium text-slate-700">WhatsApp</label>
                        <input type="tel" id="whatsapp" name="whatsapp" value="{{ old('whatsapp') }}"
                            placeholder="70000000"
                            class="mt-1 w-full rounded-lg border border-slate-300 p-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="departamento_id" class="block text-sm font-medium text-slate-700">Departamento</label>
                        <select id="departamento_id" name="departamento_id"
                            class="mt-1 w-full rounded-lg border border-slate-300 p-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Seleccione...</option>
                            @foreach ($departamentos as $departamento)
                                <option value="{{ $departamento->id }}" @selected(old('departamento_id') == $departamento->id)>
                                    {{ $departamento->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="materia_legal_id" class="block text-sm font-medium text-slate-700">Materia legal</label>
                        <select id="materia_legal_id" name="materia_legal_id"
                            class="mt-1 w-full rounded-lg border border-slate-300 p-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">Seleccione...</option>
                            @foreach ($materiasLegales as $materia)
                                <option value="{{ $materia->id }}" @selected(old('materia_legal_id') == $materia->id)>
                                    {{ $materia->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label for="captcha" class="block text-sm font-medium text-slate-700">{{ $captcha['pregunta'] }} (*)</label>
                    <input type="text" id="captcha" name="captcha" required inputmode="numeric" autocomplete="off"
                        class="mt-1 w-full sm:w-40 rounded-lg border border-slate-300 p-2.5 text-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <button type="submit"
                    class="w-full rounded-full bg-blue-600 px-6 py-3 font-semibold text-white hover:bg-blue-700">
                    Enviar consulta
                </button>

                <p class="text-xs text-center text-slate-400">
                    Al enviar, acepta que un asesor de Netley &amp; Asociados se comunique con usted para orientarle sobre su consulta.
                </p>
            </form>
        </div>
    </section>

    {{-- Contacto / footer --}}
    <footer id="contacto" class="bg-slate-900 text-slate-300">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 py-12 grid sm:grid-cols-3 gap-8">
            <div>
                <p class="font-bold text-white text-lg">Netley &amp; Asociados</p>
                <p class="mt-2 text-sm text-slate-400">Asesoramiento legal gratuito en toda Bolivia.</p>
            </div>
            <div>
                <p class="font-semibold text-white">Contacto</p>
                <a href="https://api.whatsapp.com/send?phone=59171536460&text=Hola%2C%20deseo%20realizar%20una%20consulta%20Netley"
                   target="_blank" rel="noopener" class="mt-2 block text-sm hover:text-white">WhatsApp: 715 36460</a>
            </div>
            <div>
                <p class="font-semibold text-white">Acceso interno</p>
                <div class="mt-2 flex flex-col gap-1 text-sm">
                    <a href="{{ route('admin.login') }}" class="hover:text-white">Admin</a>
                    <a href="{{ route('staff.login') }}" class="hover:text-white">Staff</a>
                    <a href="{{ route('portal.login') }}" class="hover:text-white">Portal Cliente</a>
                </div>
            </div>
        </div>
        <div class="border-t border-slate-800 py-4 text-center text-xs text-slate-500">
            &copy; {{ now()->year }} Netley &amp; Asociados.
        </div>
    </footer>

</body>
</html>
