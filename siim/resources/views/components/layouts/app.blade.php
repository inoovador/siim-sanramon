@props(['title' => null, 'breadcrumb' => []])

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title ? $title . ' · ' : '' }}{{ config('app.name', 'SIIM') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased text-ink-deep bg-brand-mist">
    <div class="min-h-screen flex">
        {{-- Sidebar --}}
        <aside class="w-64 bg-white border-r border-brand-canopy/10 flex flex-col flex-shrink-0">
            <div class="h-16 flex items-center gap-3 px-6 border-b border-brand-canopy/10">
                <div class="w-9 h-9 rounded-lg bg-brand-canopy flex items-center justify-center text-white font-serif font-bold">S</div>
                <div>
                    <p class="font-serif font-bold text-brand-canopy leading-none">SIIM</p>
                    <p class="text-[10px] text-ink-soft uppercase tracking-wider">Imagen Municipal</p>
                </div>
            </div>

            <nav class="flex-1 px-3 py-6 space-y-1 overflow-y-auto">
                <p class="px-4 text-[10px] uppercase tracking-widest text-ink-soft font-semibold mb-2">Análisis</p>
                <x-sidebar-link href="/panel" icon="dashboard">Dashboard</x-sidebar-link>
                <x-sidebar-link href="/panel/comentarios" icon="message-square">Comentarios</x-sidebar-link>
                <x-sidebar-link href="/panel/temas" icon="brain">Temas</x-sidebar-link>
                <x-sidebar-link href="/panel/chat-rag" icon="brain">Chat RAG</x-sidebar-link>
                <x-sidebar-link href="/panel/reportes" icon="bar-chart">Reportes</x-sidebar-link>

                <p class="px-4 mt-6 text-[10px] uppercase tracking-widest text-ink-soft font-semibold mb-2">Datos</p>
                <x-sidebar-link href="/panel/fuentes" icon="database">Fuentes</x-sidebar-link>

                @auth
                    @if(auth()->user()->hasRole('admin'))
                        <p class="px-4 mt-6 text-[10px] uppercase tracking-widest text-ink-soft font-semibold mb-2">Administración</p>
                        <x-sidebar-link href="/panel/usuarios" icon="users">Usuarios</x-sidebar-link>
                        <x-sidebar-link href="/panel/configuracion" icon="settings">Configuración</x-sidebar-link>
                    @endif
                @endauth

                <p class="px-4 mt-6 text-[10px] uppercase tracking-widest text-ink-soft font-semibold mb-2">Sistema</p>
                <x-sidebar-link href="/panel/auditoria" icon="history">Auditoría</x-sidebar-link>
            </nav>

            <div class="border-t border-brand-canopy/10 p-3">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-2.5 rounded-lg text-ink-soft hover:bg-brand-clay/10 hover:text-brand-clay transition">
                        <x-icon name="log-out" class="w-5 h-5" />
                        <span class="text-sm">Cerrar sesión</span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main column --}}
        <div class="flex-1 flex flex-col min-w-0">
            {{-- Topbar --}}
            <header class="h-16 bg-white border-b border-brand-canopy/10 flex items-center justify-between px-6 flex-shrink-0">
                <div class="flex items-center gap-2 text-sm">
                    @if(count($breadcrumb))
                        @foreach($breadcrumb as $idx => $crumb)
                            @if($idx > 0)
                                <span class="text-ink-soft">/</span>
                            @endif
                            <span class="{{ $idx === array_key_last($breadcrumb) ? 'text-ink-deep font-medium' : 'text-ink-soft' }}">{{ $crumb }}</span>
                        @endforeach
                    @else
                        <span class="text-ink-soft">SIIM</span>
                    @endif
                </div>

                <div class="flex items-center gap-4">
                    <div class="relative">
                        <input type="search" placeholder="Buscar..." class="w-64 pl-9 pr-3 py-2 text-sm rounded-lg border border-brand-canopy/10 focus:border-brand-canopy focus:ring-1 focus:ring-brand-canopy/20 bg-brand-mist/50" />
                        <x-icon name="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-ink-soft" />
                    </div>

                    @auth
                        <div class="flex items-center gap-3 pl-4 border-l border-brand-canopy/10">
                            <div class="w-9 h-9 rounded-full bg-brand-canopy/10 text-brand-canopy flex items-center justify-center font-serif font-bold text-sm">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <div class="text-right">
                                <p class="text-sm font-medium leading-none">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-ink-soft mt-0.5">{{ auth()->user()->roles->first()?->name ?? 'sin rol' }}</p>
                            </div>
                        </div>
                    @endauth
                </div>
            </header>

            {{-- Page content --}}
            <main class="flex-1 overflow-y-auto p-8">
                @if($title)
                    <div class="mb-6">
                        <h1 class="text-2xl font-serif font-bold text-brand-canopy">{{ $title }}</h1>
                    </div>
                @endif
                {{ $slot }}
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts@3.49.1/dist/apexcharts.min.js" defer></script>
    @livewireScripts
    @stack('scripts')
</body>
</html>
