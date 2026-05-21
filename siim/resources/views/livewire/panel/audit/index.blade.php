<?php

use function Livewire\Volt\title;
use function Livewire\Volt\state;

title('Auditoría · SIIM');

state([
    'search'        => '',
    'filterType'    => 'todos',
    'filterSeverity'=> 'todos',
    'perPage'       => 25,
    'events' => fn () => [
        ['id'=>1,  'time'=>'21/05 09:47','type'=>'admin_action','severity'=>'info',    'actor'=>'Admin SIIM',       'action'=>'Activó proveedor LLM OpenAI GPT-4o',             'resource'=>'Configuración › LLM',          'ip'=>'192.168.1.10','meta'=>['request_id'=>'req_a1b2c3','user_agent'=>'Mozilla/5.0 (Windows NT 10.0)','timestamp_iso'=>'2026-05-21T09:47:12Z']],
        ['id'=>2,  'time'=>'21/05 09:32','type'=>'nlp_run',    'severity'=>'info',    'actor'=>'Sistema',          'action'=>'Análisis NLP completado: 47 comentarios procesados','resource'=>'NLP › Batch #0291',           'ip'=>'127.0.0.1',  'meta'=>['request_id'=>'req_b2c3d4','user_agent'=>'SIIM-Worker/1.0','timestamp_iso'=>'2026-05-21T09:32:05Z']],
        ['id'=>3,  'time'=>'21/05 09:10','type'=>'auth',       'severity'=>'info',    'actor'=>'Carlos Aguirre M.','action'=>'Inicio de sesión exitoso',                        'resource'=>'Auth › Login',                 'ip'=>'192.168.1.22','meta'=>['request_id'=>'req_c3d4e5','user_agent'=>'Chrome/124.0','timestamp_iso'=>'2026-05-21T09:10:44Z']],
        ['id'=>4,  'time'=>'21/05 08:55','type'=>'error',      'severity'=>'warning', 'actor'=>'Sistema',          'action'=>'Llamada LLM falló: HTTP 429 rate limit',          'resource'=>'LLM › OpenAI API',             'ip'=>'127.0.0.1',  'meta'=>['request_id'=>'req_d4e5f6','user_agent'=>'SIIM-Worker/1.0','timestamp_iso'=>'2026-05-21T08:55:31Z']],
        ['id'=>5,  'time'=>'21/05 08:40','type'=>'nlp_run',    'severity'=>'info',    'actor'=>'Sistema',          'action'=>'Importación CSV: 234 filas procesadas, 12 rechazadas','resource'=>'Ingesta › CSV Upload #88',  'ip'=>'127.0.0.1',  'meta'=>['request_id'=>'req_e5f6g7','user_agent'=>'SIIM-Worker/1.0','timestamp_iso'=>'2026-05-21T08:40:19Z']],
        ['id'=>6,  'time'=>'21/05 08:15','type'=>'admin_action','severity'=>'info',   'actor'=>'Admin SIIM',       'action'=>'Creó usuario Kimberly Medina P. (rol: admin)',     'resource'=>'Usuarios › id=2',              'ip'=>'192.168.1.10','meta'=>['request_id'=>'req_f6g7h8','user_agent'=>'Mozilla/5.0 (Windows NT 10.0)','timestamp_iso'=>'2026-05-21T08:15:02Z']],
        ['id'=>7,  'time'=>'21/05 07:58','type'=>'system',     'severity'=>'critical','actor'=>'Sistema',          'action'=>'Token Meta Graph expirado — ingesta Facebook pausada','resource'=>'Fuentes › Facebook Graph', 'ip'=>'127.0.0.1',  'meta'=>['request_id'=>'req_g7h8i9','user_agent'=>'SIIM-Scheduler/1.0','timestamp_iso'=>'2026-05-21T07:58:00Z']],
        ['id'=>8,  'time'=>'21/05 07:42','type'=>'admin_action','severity'=>'info',   'actor'=>'Sofía Quispe R.',  'action'=>'Generó reporte "Sentimiento semanal" (PDF)',        'resource'=>'Reportes › id=47',             'ip'=>'192.168.1.31','meta'=>['request_id'=>'req_h8i9j0','user_agent'=>'Chrome/124.0','timestamp_iso'=>'2026-05-21T07:42:33Z']],
        ['id'=>9,  'time'=>'21/05 07:30','type'=>'system',     'severity'=>'info',    'actor'=>'Sistema',          'action'=>'Ingesta Facebook reanudada tras renovación de token','resource'=>'Fuentes › Facebook Graph',   'ip'=>'127.0.0.1',  'meta'=>['request_id'=>'req_i9j0k1','user_agent'=>'SIIM-Scheduler/1.0','timestamp_iso'=>'2026-05-21T07:30:11Z']],
        ['id'=>10, 'time'=>'21/05 07:15','type'=>'error',      'severity'=>'warning', 'actor'=>'Sistema',          'action'=>'NLP falló para comentario id=4721 — JSON malformado','resource'=>'NLP › Comentario #4721',    'ip'=>'127.0.0.1',  'meta'=>['request_id'=>'req_j0k1l2','user_agent'=>'SIIM-Worker/1.0','timestamp_iso'=>'2026-05-21T07:15:58Z']],
        ['id'=>11, 'time'=>'20/05 18:44','type'=>'admin_action','severity'=>'warning','actor'=>'Admin SIIM',       'action'=>'Modificó prompt sentiment_analysis a v4',          'resource'=>'Configuración › Prompts',      'ip'=>'192.168.1.10','meta'=>['request_id'=>'req_k1l2m3','user_agent'=>'Mozilla/5.0 (Windows NT 10.0)','timestamp_iso'=>'2026-05-20T18:44:07Z']],
        ['id'=>12, 'time'=>'20/05 17:30','type'=>'system',     'severity'=>'warning', 'actor'=>'Sistema',          'action'=>'Cost guard: $4.20 gastado de $5.00 diarios (84%)', 'resource'=>'Presupuesto › LLM Daily',      'ip'=>'127.0.0.1',  'meta'=>['request_id'=>'req_l2m3n4','user_agent'=>'SIIM-Monitor/1.0','timestamp_iso'=>'2026-05-20T17:30:00Z']],
        ['id'=>13, 'time'=>'20/05 16:20','type'=>'admin_action','severity'=>'critical','actor'=>'Marco Trejos L.', 'action'=>'Cambió rol de Diana Loayza V.: analyst → admin',   'resource'=>'Usuarios › id=6',              'ip'=>'192.168.1.45','meta'=>['request_id'=>'req_m3n4o5','user_agent'=>'Chrome/124.0','timestamp_iso'=>'2026-05-20T16:20:44Z']],
        ['id'=>14, 'time'=>'20/05 15:05','type'=>'system',     'severity'=>'info',    'actor'=>'Sistema',          'action'=>'Indexación Meilisearch completada: 12.450 docs',    'resource'=>'Search › Meilisearch',         'ip'=>'127.0.0.1',  'meta'=>['request_id'=>'req_n4o5p6','user_agent'=>'SIIM-Indexer/1.0','timestamp_iso'=>'2026-05-20T15:05:22Z']],
        ['id'=>15, 'time'=>'20/05 14:50','type'=>'auth',       'severity'=>'info',    'actor'=>'Pedro Salinas G.', 'action'=>'Sesión expirada por inactividad (30 min)',          'resource'=>'Auth › Session',               'ip'=>'192.168.1.38','meta'=>['request_id'=>'req_o5p6q7','user_agent'=>'Firefox/125.0','timestamp_iso'=>'2026-05-20T14:50:00Z']],
        ['id'=>16, 'time'=>'20/05 13:33','type'=>'nlp_run',    'severity'=>'info',    'actor'=>'Sistema',          'action'=>'Análisis NLP completado: 83 comentarios procesados','resource'=>'NLP › Batch #0290',            'ip'=>'127.0.0.1',  'meta'=>['request_id'=>'req_p6q7r8','user_agent'=>'SIIM-Worker/1.0','timestamp_iso'=>'2026-05-20T13:33:14Z']],
        ['id'=>17, 'time'=>'20/05 12:10','type'=>'admin_action','severity'=>'info',   'actor'=>'Kimberly Medina P.','action'=>'Exportó CSV de comentarios del canal Instagram', 'resource'=>'Comentarios › Export #12',     'ip'=>'192.168.1.12','meta'=>['request_id'=>'req_q7r8s9','user_agent'=>'Chrome/124.0','timestamp_iso'=>'2026-05-20T12:10:05Z']],
        ['id'=>18, 'time'=>'20/05 11:00','type'=>'auth',       'severity'=>'info',    'actor'=>'Rosa Núñez F.',    'action'=>'Inicio de sesión exitoso',                         'resource'=>'Auth › Login',                 'ip'=>'192.168.1.55','meta'=>['request_id'=>'req_r8s9t0','user_agent'=>'Chrome/124.0','timestamp_iso'=>'2026-05-20T11:00:33Z']],
        ['id'=>19, 'time'=>'20/05 10:45','type'=>'system',     'severity'=>'info',    'actor'=>'Sistema',          'action'=>'Backup automático completado: 2.3 GB almacenados',  'resource'=>'Sistema › Backup diario',      'ip'=>'127.0.0.1',  'meta'=>['request_id'=>'req_s9t0u1','user_agent'=>'SIIM-Backup/1.0','timestamp_iso'=>'2026-05-20T10:45:00Z']],
        ['id'=>20, 'time'=>'20/05 09:22','type'=>'error',      'severity'=>'error',   'actor'=>'Sistema',          'action'=>'Fallo conexión PostgreSQL: timeout 30s',            'resource'=>'DB › PostgreSQL Primary',      'ip'=>'127.0.0.1',  'meta'=>['request_id'=>'req_t0u1v2','user_agent'=>'SIIM-Health/1.0','timestamp_iso'=>'2026-05-20T09:22:47Z']],
        ['id'=>21, 'time'=>'19/05 18:05','type'=>'nlp_run',    'severity'=>'info',    'actor'=>'Sistema',          'action'=>'Clasificación temática batch: 156 comentarios',    'resource'=>'NLP › Topics Batch #0289',     'ip'=>'127.0.0.1',  'meta'=>['request_id'=>'req_u1v2w3','user_agent'=>'SIIM-Worker/1.0','timestamp_iso'=>'2026-05-19T18:05:09Z']],
        ['id'=>22, 'time'=>'19/05 16:30','type'=>'admin_action','severity'=>'info',   'actor'=>'Carlos Aguirre M.','action'=>'Archivó tema "Fiestas patronales" (42 comentarios)','resource'=>'Temas › id=18',               'ip'=>'192.168.1.22','meta'=>['request_id'=>'req_v2w3x4','user_agent'=>'Chrome/124.0','timestamp_iso'=>'2026-05-19T16:30:22Z']],
        ['id'=>23, 'time'=>'19/05 15:15','type'=>'system',     'severity'=>'warning', 'actor'=>'Sistema',          'action'=>'Memoria RAM al 87% — proceso NLP pausado 5 min',   'resource'=>'Sistema › Resources',          'ip'=>'127.0.0.1',  'meta'=>['request_id'=>'req_w3x4y5','user_agent'=>'SIIM-Monitor/1.0','timestamp_iso'=>'2026-05-19T15:15:30Z']],
        ['id'=>24, 'time'=>'19/05 14:00','type'=>'auth',       'severity'=>'info',    'actor'=>'Diana Loayza V.',  'action'=>'Cambio de contraseña exitoso',                      'resource'=>'Auth › Password',              'ip'=>'192.168.1.31','meta'=>['request_id'=>'req_x4y5z6','user_agent'=>'Firefox/125.0','timestamp_iso'=>'2026-05-19T14:00:18Z']],
        ['id'=>25, 'time'=>'19/05 11:22','type'=>'nlp_run',    'severity'=>'info',    'actor'=>'Sistema',          'action'=>'Análisis NLP fallido recuperado: reintento exitoso','resource'=>'NLP › Comentario #4710',       'ip'=>'127.0.0.1',  'meta'=>['request_id'=>'req_y5z6a7','user_agent'=>'SIIM-Worker/1.0','timestamp_iso'=>'2026-05-19T11:22:00Z']],
        ['id'=>26, 'time'=>'19/05 10:10','type'=>'admin_action','severity'=>'info',   'actor'=>'Admin SIIM',       'action'=>'Agregó fuente Instagram @munisanramon2025',         'resource'=>'Fuentes › Instagram',          'ip'=>'192.168.1.10','meta'=>['request_id'=>'req_z6a7b8','user_agent'=>'Mozilla/5.0 (Windows NT 10.0)','timestamp_iso'=>'2026-05-19T10:10:44Z']],
        ['id'=>27, 'time'=>'18/05 17:48','type'=>'system',     'severity'=>'info',    'actor'=>'Sistema',          'action'=>'Actualización automática vocabulario NLP completada','resource'=>'NLP › Vocabulary v2.1',       'ip'=>'127.0.0.1',  'meta'=>['request_id'=>'req_a7b8c9','user_agent'=>'SIIM-Updater/1.0','timestamp_iso'=>'2026-05-18T17:48:55Z']],
        ['id'=>28, 'time'=>'18/05 14:30','type'=>'error',      'severity'=>'critical','actor'=>'Sistema',          'action'=>'LLM respondió contenido tóxico — filtro activado',  'resource'=>'LLM › Safety Filter',         'ip'=>'127.0.0.1',  'meta'=>['request_id'=>'req_b8c9d0','user_agent'=>'SIIM-Worker/1.0','timestamp_iso'=>'2026-05-18T14:30:07Z']],
        ['id'=>29, 'time'=>'18/05 11:05','type'=>'auth',       'severity'=>'info',    'actor'=>'Luis Delgado H.',  'action'=>'Primer inicio de sesión tras invitación',           'resource'=>'Auth › Onboarding',            'ip'=>'192.168.1.61','meta'=>['request_id'=>'req_c9d0e1','user_agent'=>'Chrome/124.0','timestamp_iso'=>'2026-05-18T11:05:29Z']],
        ['id'=>30, 'time'=>'17/05 09:00','type'=>'system',     'severity'=>'info',    'actor'=>'Sistema',          'action'=>'SIIM v1.0.0 desplegado correctamente en producción','resource'=>'Sistema › Deploy',             'ip'=>'127.0.0.1',  'meta'=>['request_id'=>'req_d0e1f2','user_agent'=>'SIIM-Deploy/1.0','timestamp_iso'=>'2026-05-17T09:00:00Z']],
    ],
]);

?>

<div>
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-serif font-bold text-brand-canopy">Auditoría</h1>
            <p class="mt-1 text-sm text-ink-soft">Bitácora de acciones administrativas y procesos NLP</p>
        </div>
        <button class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-brand-canopy text-brand-canopy text-sm font-semibold hover:bg-brand-canopy/5 transition-colors">
            <x-icon name="file-text" class="w-4 h-4" />
            Exportar CSV
        </button>
    </div>

    {{-- KPI row --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach([
            ['label'=>'Eventos hoy',           'value'=>'247', 'icon'=>'bar-chart','color'=>'brand-canopy','sub'=>'en las últimas 24h'],
            ['label'=>'Acciones críticas',     'value'=>'8',   'icon'=>'history',  'color'=>'brand-clay',  'sub'=>'esta semana'],
            ['label'=>'Análisis NLP exitosos', 'value'=>'189', 'icon'=>'brain',    'color'=>'brand-river', 'sub'=>'últimas 24h'],
            ['label'=>'Errores LLM',           'value'=>'3',   'icon'=>'database', 'color'=>'brand-gold',  'sub'=>'últimas 24h'],
        ] as $kpi)
        <div class="card flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-{{ $kpi['color'] }}/10 text-{{ $kpi['color'] }} flex items-center justify-center flex-shrink-0 mt-0.5">
                <x-icon :name="$kpi['icon']" class="w-5 h-5" />
            </div>
            <div>
                <p class="text-xs uppercase tracking-wider text-ink-soft font-semibold leading-tight">{{ $kpi['label'] }}</p>
                <p class="text-2xl font-serif font-bold text-brand-canopy mt-1">{{ $kpi['value'] }}</p>
                <p class="text-xs text-ink-soft mt-0.5">{{ $kpi['sub'] }}</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <div class="card mb-5">
        <div class="flex flex-col gap-3">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-ink-soft">
                        <x-icon name="search" class="w-4 h-4" />
                    </div>
                    <input
                        wire:model.live.debounce.300ms="search"
                        type="text"
                        placeholder="Buscar acción, usuario, recurso..."
                        class="w-full pl-9 pr-4 py-2 rounded-lg border border-brand-canopy/20 text-sm text-ink-deep focus:outline-none focus:ring-2 focus:ring-brand-canopy/30 focus:border-brand-canopy/40"
                    />
                </div>
                <select wire:model.live="filterType" class="rounded-lg border border-brand-canopy/20 px-3 py-2 text-sm text-ink-deep focus:outline-none focus:ring-2 focus:ring-brand-canopy/30 bg-white">
                    <option value="todos">Todos los tipos</option>
                    <option value="admin_action">Acción admin</option>
                    <option value="nlp_run">Proceso NLP</option>
                    <option value="auth">Autenticación</option>
                    <option value="system">Sistema</option>
                    <option value="error">Error</option>
                </select>
                <select wire:model.live="filterSeverity" class="rounded-lg border border-brand-canopy/20 px-3 py-2 text-sm text-ink-deep focus:outline-none focus:ring-2 focus:ring-brand-canopy/30 bg-white">
                    <option value="todos">Todas las severidades</option>
                    <option value="info">Info</option>
                    <option value="warning">Warning</option>
                    <option value="error">Error</option>
                    <option value="critical">Crítico</option>
                </select>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 items-center">
                <div class="flex gap-3 flex-1">
                    <input type="date" class="rounded-lg border border-brand-canopy/20 px-3 py-2 text-sm text-ink-deep focus:outline-none focus:ring-2 focus:ring-brand-canopy/30 bg-white" value="2026-05-17" />
                    <input type="date" class="rounded-lg border border-brand-canopy/20 px-3 py-2 text-sm text-ink-deep focus:outline-none focus:ring-2 focus:ring-brand-canopy/30 bg-white" value="2026-05-21" />
                </div>
                <button class="text-xs text-brand-river hover:text-brand-canopy underline whitespace-nowrap transition-colors">Limpiar filtros</button>
            </div>
        </div>
    </div>

    {{-- Tabla de eventos --}}
    <div class="card overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wider text-ink-soft border-b border-brand-canopy/10 bg-brand-mist/60">
                        <th class="px-4 py-3 font-semibold whitespace-nowrap">Hora</th>
                        <th class="px-4 py-3 font-semibold">Tipo</th>
                        <th class="px-4 py-3 font-semibold">Sev.</th>
                        <th class="px-4 py-3 font-semibold">Actor</th>
                        <th class="px-4 py-3 font-semibold">Acción</th>
                        <th class="px-4 py-3 font-semibold">Recurso</th>
                        <th class="px-4 py-3 font-semibold">IP / Origen</th>
                    </tr>
                </thead>
                <tbody x-data="{ expanded: null }" class="divide-y divide-brand-canopy/5">
                    @foreach($events as $ev)
                    @php
                        $typeConfig = match($ev['type']) {
                            'admin_action' => ['label' => 'Admin',    'class' => 'bg-brand-canopy text-white'],
                            'nlp_run'      => ['label' => 'NLP',      'class' => 'bg-brand-river text-white'],
                            'auth'         => ['label' => 'Auth',     'class' => 'bg-brand-gold text-white'],
                            'system'       => ['label' => 'Sistema',  'class' => 'bg-ink-soft/15 text-ink-soft'],
                            'error'        => ['label' => 'Error',    'class' => 'bg-brand-clay text-white'],
                            default        => ['label' => $ev['type'],'class' => 'bg-ink-soft/15 text-ink-soft'],
                        };
                        $sevConfig = match($ev['severity']) {
                            'info'     => ['dot' => 'bg-green-500',       'label' => 'Info'],
                            'warning'  => ['dot' => 'bg-brand-gold',      'label' => 'Warning'],
                            'error'    => ['dot' => 'bg-brand-clay',      'label' => 'Error'],
                            'critical' => ['dot' => 'bg-red-700',         'label' => 'Crítico'],
                            default    => ['dot' => 'bg-ink-soft/40',     'label' => $ev['severity']],
                        };
                        $isSystem = $ev['actor'] === 'Sistema';
                        $actorInitials = $isSystem ? 'SYS' : collect(explode(' ', $ev['actor']))->take(2)->map(fn($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('');
                        $metaJson = json_encode($ev['meta'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    @endphp
                    <tr
                        class="hover:bg-brand-mist/40 transition-colors cursor-pointer"
                        @click="expanded = expanded === {{ $ev['id'] }} ? null : {{ $ev['id'] }}"
                    >
                        <td class="px-4 py-3 text-xs text-ink-soft whitespace-nowrap font-mono">{{ $ev['time'] }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold whitespace-nowrap {{ $typeConfig['class'] }}">{{ $typeConfig['label'] }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1.5" title="{{ $sevConfig['label'] }}">
                                <span class="w-2 h-2 rounded-full flex-shrink-0 {{ $sevConfig['dot'] }}"></span>
                                <span class="text-xs text-ink-soft hidden sm:inline">{{ $sevConfig['label'] }}</span>
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full {{ $isSystem ? 'bg-ink-soft/15 text-ink-soft' : 'bg-brand-canopy/10 text-brand-canopy' }} flex items-center justify-center flex-shrink-0">
                                    <span class="font-serif text-[9px] font-bold">{{ $actorInitials }}</span>
                                </div>
                                <span class="text-xs text-ink-deep whitespace-nowrap">{{ $ev['actor'] }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-ink-deep max-w-xs">
                            <div class="flex items-center gap-2">
                                <span class="truncate text-xs">{{ $ev['action'] }}</span>
                                <svg
                                    x-bind:class="expanded === {{ $ev['id'] }} ? 'rotate-180' : ''"
                                    class="w-3 h-3 text-ink-soft flex-shrink-0 transition-transform"
                                    fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                                ><path d="M19 9l-7 7-7-7"/></svg>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-xs text-ink-soft whitespace-nowrap">{{ $ev['resource'] }}</td>
                        <td class="px-4 py-3 text-xs text-ink-soft font-mono whitespace-nowrap">{{ $ev['ip'] }}</td>
                    </tr>
                    {{-- Fila expandible con metadata --}}
                    <tr x-show="expanded === {{ $ev['id'] }}" class="bg-brand-mist/30">
                        <td colspan="7" class="px-4 py-3">
                            <div
                                x-show="expanded === {{ $ev['id'] }}"
                                x-transition:enter="transition ease-out duration-150"
                                x-transition:enter-start="opacity-0 -translate-y-1"
                                x-transition:enter-end="opacity-100 translate-y-0"
                                class="rounded-lg border border-brand-canopy/10 bg-white p-4"
                            >
                                <p class="text-xs font-semibold text-ink-soft uppercase tracking-wider mb-2">Metadata del evento</p>
                                <pre class="text-xs text-ink-deep font-mono overflow-x-auto leading-relaxed whitespace-pre-wrap">{{ $metaJson }}</pre>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Paginación --}}
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-4 py-3 border-t border-brand-canopy/10 bg-brand-mist/30">
            <div class="flex items-center gap-2 text-xs text-ink-soft">
                Mostrando
                <select wire:model.live="perPage" class="rounded border border-brand-canopy/20 px-2 py-1 text-xs text-ink-deep focus:outline-none focus:ring-1 focus:ring-brand-canopy/30 bg-white">
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                por página · <span class="font-medium text-ink-deep">30</span> eventos totales
            </div>
            <div class="flex items-center gap-1">
                <button class="px-3 py-1.5 rounded-lg text-xs font-medium border border-brand-canopy/20 text-ink-soft hover:bg-brand-mist transition-colors disabled:opacity-40" disabled>Anterior</button>
                <button class="px-3 py-1.5 rounded-lg text-xs font-semibold bg-brand-canopy text-white">1</button>
                <button class="px-3 py-1.5 rounded-lg text-xs font-medium border border-brand-canopy/20 text-ink-soft hover:bg-brand-mist transition-colors disabled:opacity-40" disabled>Siguiente</button>
            </div>
        </div>
    </div>
</div>
