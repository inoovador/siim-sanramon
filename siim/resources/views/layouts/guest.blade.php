<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>{{ config('app.name', 'SIIM') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-ink-deep">
        <div class="min-h-screen flex">
            {{-- Panel izquierdo: branding --}}
            <aside class="hidden lg:flex w-1/2 bg-brand-canopy text-white relative overflow-hidden flex-col justify-between p-12">
                <div>
                    <p class="text-xs uppercase tracking-widest opacity-80">Municipalidad Distrital</p>
                    <h1 class="mt-2 text-4xl font-serif font-bold">San Ramón</h1>
                </div>
                <div>
                    <h2 class="text-3xl font-serif font-semibold leading-tight">SIIM</h2>
                    <p class="mt-1 text-sm opacity-90">Sistema Inteligente de Imagen Municipal</p>
                    <p class="mt-6 text-sm opacity-80 max-w-sm">Análisis de percepción ciudadana con inteligencia artificial y procesamiento de lenguaje natural.</p>
                </div>
                <p class="text-xs opacity-60">Chanchamayo · Junín · Perú</p>

                {{-- Overlay textura sutil --}}
                <div class="absolute inset-0 bg-gradient-to-br from-brand-canopy via-brand-canopy/95 to-emerald-900/40 pointer-events-none"></div>
                <div class="absolute inset-0 z-10 pointer-events-none" style="background-image: radial-gradient(circle at 20% 80%, rgba(224,162,74,0.08) 0%, transparent 50%), radial-gradient(circle at 80% 20%, rgba(30,127,168,0.08) 0%, transparent 50%);"></div>
            </aside>

            {{-- Panel derecho: form --}}
            <main class="flex-1 flex flex-col justify-center items-center bg-brand-mist px-6 py-12 lg:px-12">
                <div class="w-full max-w-md">
                    <div class="lg:hidden text-center mb-8">
                        <h1 class="text-3xl font-serif font-bold text-brand-canopy">SIIM</h1>
                        <p class="text-sm text-ink-soft">Sistema Inteligente de Imagen Municipal</p>
                    </div>

                    {{ $slot }}

                    <p class="mt-8 text-center text-xs text-ink-soft">
                        © {{ date('Y') }} Municipalidad Distrital de San Ramón
                    </p>
                </div>
            </main>
        </div>
    </body>
</html>
