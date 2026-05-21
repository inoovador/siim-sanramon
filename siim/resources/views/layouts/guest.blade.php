<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ config('app.name', 'SIIM') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-ink-deep bg-gradient-to-br from-brand-mist via-[#EDE8DC] to-[#DDE7E0] min-h-screen flex items-center justify-center p-4 lg:p-8">

    <main class="w-full max-w-5xl bg-white rounded-3xl shadow-brand-lg overflow-hidden flex flex-col lg:flex-row min-h-[600px]">

        {{-- LEFT pane: brand + illustration --}}
        <aside class="lg:w-1/2 bg-gradient-to-br from-brand-canopy/5 via-white to-brand-river/5 p-8 lg:p-12 flex flex-col justify-between relative overflow-hidden">

            {{-- Decorative blobs --}}
            <div class="absolute -top-20 -left-20 w-80 h-80 bg-brand-canopy/5 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-20 -right-20 w-72 h-72 bg-brand-river/5 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative">
                <div class="flex items-center gap-3">
                    <img src="/images/logo-san-ramon.png" alt="Municipalidad San Ramón" class="h-14 w-auto" />
                    <div>
                        <p class="font-serif font-bold text-brand-canopy leading-tight">SIIM</p>
                        <p class="text-[10px] text-ink-soft uppercase tracking-wider">Imagen Municipal</p>
                    </div>
                </div>
            </div>

            <div class="relative flex-1 flex items-center justify-center my-6">
                <img src="/images/auth-illustration.png" alt="Asistente SIIM" class="max-h-72 w-auto" />
            </div>

            <div class="relative text-center">
                <h2 class="text-xl lg:text-2xl font-serif font-semibold text-ink-deep leading-snug">Análisis inteligente de la percepción ciudadana</h2>
                <p class="mt-2 text-sm text-ink-soft max-w-md mx-auto">Convertimos la voz de San Ramón en decisiones informadas.</p>
            </div>
        </aside>

        {{-- RIGHT pane: form --}}
        <section class="lg:w-1/2 p-8 lg:p-14 flex flex-col justify-center bg-white">
            {{ $slot }}

            <p class="mt-10 text-center text-xs text-ink-soft/70">
                © {{ date('Y') }} Municipalidad Distrital de San Ramón
            </p>
        </section>

    </main>
</body>
</html>
