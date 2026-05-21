<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SIIM — Sistema Inteligente de Imagen Municipal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex items-center justify-center">
    <main class="card max-w-2xl text-center">
        <p class="text-xs uppercase tracking-widest text-brand-canopy/70 font-semibold">Municipalidad Distrital de San Ramón</p>
        <h1 class="mt-2 text-4xl font-serif font-bold text-brand-canopy">SIIM</h1>
        <p class="mt-1 text-ink-soft">Sistema Inteligente de Imagen Municipal</p>
        <p class="mt-6 text-sm text-ink-soft">
            Plataforma de análisis de percepción ciudadana con IA y NLP.
            <br/>Versión <code class="text-brand-river">0.1.0-F0</code> — Scaffold inicial.
        </p>
        <div class="mt-8 flex gap-3 justify-center">
            <a href="/health" class="btn-primary">Health check</a>
            <a href="/login" class="px-4 py-2 rounded-lg border border-brand-canopy text-brand-canopy hover:bg-brand-canopy/5">Acceso funcionarios</a>
        </div>
    </main>
</body>
</html>
