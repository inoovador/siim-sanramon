<!DOCTYPE html>
<html lang="es-PE">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Encuesta ciudadana · SIIM' }}</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-brand-mist text-ink-deep">
    <a href="#contenido" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-white focus:px-4 focus:py-2 focus:text-brand-canopy focus:shadow-brand-lg">
        Saltar al contenido
    </a>

    <header class="border-b border-brand-canopy/10 bg-white">
        <div class="mx-auto flex max-w-5xl items-center justify-between gap-4 px-4 py-4 sm:px-6">
            <a href="{{ url('/') }}" class="flex min-w-0 items-center gap-3 rounded-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-gold">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-canopy font-serif text-lg font-bold text-white" aria-hidden="true">SR</span>
                <span class="min-w-0">
                    <span class="block truncate font-serif font-semibold text-brand-canopy">Municipalidad Distrital de San Ramón</span>
                    <span class="block text-xs text-ink-soft">Sistema Inteligente de Imagen Municipal</span>
                </span>
            </a>
            <a href="{{ route('survey.show') }}" class="shrink-0 text-sm font-semibold text-brand-river underline decoration-brand-gold decoration-2 underline-offset-4 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-brand-gold">
                Encuesta
            </a>
        </div>
    </header>

    <main id="contenido">
        {{ $slot }}
    </main>

    <footer class="mt-12 border-t border-brand-canopy/10 bg-white">
        <div class="mx-auto max-w-5xl px-4 py-6 text-center text-xs text-ink-soft sm:px-6">
            Municipalidad Distrital de San Ramón · Participación ciudadana responsable
        </div>
    </footer>

    @livewireScripts
</body>
</html>
