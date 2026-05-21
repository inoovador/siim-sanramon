<?php

use function Livewire\Volt\title;
use function Livewire\Volt\state;

title('Reportes · SIIM');

state([
    'activeTab' => 'plantillas',
    'toast'     => '',
]);

$showToast = function (string $nombre) {
    $this->toast = "Reporte \"{$nombre}\" agregándose a cola...";
    $this->dispatch('show-toast');
};

?>

<div
    x-data="{
        activeTab: 'plantillas',
        modalOpen: false,
        toastVisible: false,
        toastMsg: '',
        form: { nombre: '', plantilla: '', desde: '', hasta: '', canal: 'todos', tema: 'todos', formato: 'pdf' },
        showToast(msg) { this.toastMsg = msg; this.toastVisible = true; setTimeout(() => this.toastVisible = false, 3500); }
    }"
    x-on:show-toast.window="showToast($wire.toast)"
>

    {{-- Toast --}}
    <div
        x-show="toastVisible"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed bottom-6 right-6 z-50 bg-brand-canopy text-white px-5 py-3 rounded-xl shadow-brand-lg flex items-center gap-3 text-sm font-medium"
        style="display:none;"
    >
        <svg class="w-5 h-5 text-brand-gold flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span x-text="toastMsg"></span>
    </div>

    {{-- Modal Nuevo Reporte --}}
    <div
        x-show="modalOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        class="fixed inset-0 z-40 flex items-center justify-center p-4"
        style="display:none;"
    >
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" x-on:click="modalOpen = false"></div>
        <div class="relative bg-white rounded-2xl shadow-brand-lg w-full max-w-lg p-6 z-10">
            <div class="flex items-center justify-between mb-5">
                <h2 class="font-serif text-xl font-bold text-brand-canopy">Nuevo reporte</h2>
                <button x-on:click="modalOpen = false" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100 text-ink-soft transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="flex flex-col gap-4">
                <div>
                    <label class="block text-xs font-medium text-ink-soft mb-1">Nombre del reporte</label>
                    <input x-model="form.nombre" type="text" placeholder="Ej. Análisis semana 21 — Mayo 2026" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-canopy/30 focus:border-brand-canopy/50"/>
                </div>
                <div>
                    <label class="block text-xs font-medium text-ink-soft mb-1">Plantilla</label>
                    <select x-model="form.plantilla" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-canopy/30">
                        <option value="">Seleccionar plantilla...</option>
                        <option value="ejecutivo">Reporte ejecutivo semanal</option>
                        <option value="tema">Análisis por tema</option>
                        <option value="tendencia">Tendencia mensual</option>
                        <option value="brutos">Export datos brutos</option>
                        <option value="canales">Reporte de canales</option>
                        <option value="ranking">Top quejas / elogios</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-ink-soft mb-1">Desde</label>
                        <input x-model="form.desde" type="date" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-canopy/30"/>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-ink-soft mb-1">Hasta</label>
                        <input x-model="form.hasta" type="date" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-canopy/30"/>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-ink-soft mb-1">Canal</label>
                        <select x-model="form.canal" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-canopy/30">
                            <option value="todos">Todos</option>
                            <option value="facebook">Facebook</option>
                            <option value="twitter">Twitter</option>
                            <option value="instagram">Instagram</option>
                            <option value="formulario">Formulario</option>
                            <option value="email">Email</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-ink-soft mb-1">Tema</label>
                        <select x-model="form.tema" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-brand-canopy/30">
                            <option value="todos">Todos</option>
                            <option value="obras">Obras públicas</option>
                            <option value="seguridad">Seguridad</option>
                            <option value="salud">Salud</option>
                            <option value="transporte">Transporte</option>
                            <option value="educacion">Educación</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-ink-soft mb-2">Formato de salida</label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" x-model="form.formato" value="pdf" class="accent-brand-clay"/> <span class="text-sm text-ink-deep">PDF</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" x-model="form.formato" value="excel" class="accent-brand-canopy"/> <span class="text-sm text-ink-deep">Excel</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="flex gap-3 mt-6 justify-end">
                <button x-on:click="modalOpen = false" class="px-4 py-2 text-sm rounded-lg border border-gray-200 text-ink-soft hover:bg-gray-50 transition-colors">Cancelar</button>
                <button
                    x-on:click="modalOpen = false; showToast('Reporte \'' + (form.nombre || 'Sin nombre') + '\' agregándose a cola...')"
                    class="btn-primary px-5 py-2 text-sm"
                >Generar reporte</button>
            </div>
        </div>
    </div>

    {{-- Header --}}
    <div class="card flex items-center justify-between mb-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-brand-canopy/10 rounded-xl flex items-center justify-center text-brand-canopy flex-shrink-0">
                <x-icon name="bar-chart" class="w-7 h-7" />
            </div>
            <div>
                <h1 class="font-serif text-2xl font-bold text-brand-canopy">Reportes</h1>
                <p class="text-sm text-ink-soft mt-0.5">Genera y descarga reportes ejecutivos del análisis ciudadano</p>
            </div>
        </div>
        <button x-on:click="modalOpen = true" class="btn-primary flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Nuevo reporte
        </button>
    </div>

    {{-- KPIs --}}
    <div class="grid grid-cols-3 gap-4 mb-4">
        <div class="card flex items-center gap-4">
            <div class="w-11 h-11 bg-brand-canopy/10 rounded-xl flex items-center justify-center flex-shrink-0">
                <x-icon name="file-text" class="w-6 h-6 text-brand-canopy" />
            </div>
            <div>
                <p class="text-2xl font-bold font-serif text-brand-canopy">47</p>
                <p class="text-xs text-ink-soft">Reportes generados</p>
            </div>
        </div>
        <div class="card flex items-center gap-4">
            <div class="w-11 h-11 bg-brand-river/10 rounded-xl flex items-center justify-center flex-shrink-0">
                <x-icon name="history" class="w-6 h-6 text-brand-river" />
            </div>
            <div>
                <p class="text-2xl font-bold font-serif text-brand-river">128</p>
                <p class="text-xs text-ink-soft">Descargas este mes</p>
            </div>
        </div>
        <div class="card flex items-center gap-4">
            <div class="w-11 h-11 bg-brand-gold/15 rounded-xl flex items-center justify-center flex-shrink-0">
                <x-icon name="bar-chart" class="w-6 h-6 text-brand-gold" />
            </div>
            <div>
                <p class="text-sm font-bold font-serif text-brand-gold leading-tight">Sentimiento semanal</p>
                <p class="text-xs text-ink-soft">Reporte más popular</p>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-1 mb-4 border-b border-gray-100 pb-0">
        <button
            x-on:click="activeTab = 'plantillas'"
            :class="activeTab === 'plantillas' ? 'border-b-2 border-brand-canopy text-brand-canopy font-medium' : 'text-ink-soft hover:text-ink-deep'"
            class="px-4 py-2.5 text-sm transition-colors -mb-px"
        >Plantillas</button>
        <button
            x-on:click="activeTab = 'generados'"
            :class="activeTab === 'generados' ? 'border-b-2 border-brand-canopy text-brand-canopy font-medium' : 'text-ink-soft hover:text-ink-deep'"
            class="px-4 py-2.5 text-sm transition-colors -mb-px"
        >Reportes generados</button>
    </div>

    {{-- Tab: Plantillas --}}
    <div x-show="activeTab === 'plantillas'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
        <div class="grid grid-cols-2 gap-4">

            @php
            $plantillas = [
                ['icono' => 'file-text',    'titulo' => 'Reporte ejecutivo semanal',  'desc' => 'Resumen de sentimiento, top temas y tabla de muestra representativa del período.',    'formato' => 'PDF',   'color' => 'brand-clay'],
                ['icono' => 'brain',        'titulo' => 'Análisis por tema',           'desc' => 'Distribución de sentimiento por tema con ejemplos verbatim de comentarios clave.',    'formato' => 'PDF',   'color' => 'brand-clay'],
                ['icono' => 'bar-chart',    'titulo' => 'Tendencia mensual',           'desc' => 'Charts de evolución temporal y comparación entre períodos consecutivos.',              'formato' => 'PDF',   'color' => 'brand-clay'],
                ['icono' => 'database',     'titulo' => 'Export datos brutos',         'desc' => 'Exporta todos los comentarios con sus scores de sentimiento y metadatos de canal.',   'formato' => 'Excel', 'color' => 'brand-canopy'],
                ['icono' => 'message-square','titulo' => 'Reporte de canales',         'desc' => 'Comparativa de volumen y sentimiento entre redes sociales y formulario web.',         'formato' => 'PDF',   'color' => 'brand-clay'],
                ['icono' => 'history',      'titulo' => 'Top quejas / elogios',        'desc' => 'Ranking de comentarios más destacados, positivos y negativos, del período.',          'formato' => 'PDF',   'color' => 'brand-clay'],
            ];
            @endphp

            @foreach ($plantillas as $p)
            <div class="card flex flex-col gap-3 hover:shadow-brand transition-shadow">
                <div class="flex items-start justify-between">
                    <div class="w-10 h-10 bg-brand-canopy/10 rounded-xl flex items-center justify-center text-brand-canopy flex-shrink-0">
                        <x-icon name="{{ $p['icono'] }}" class="w-5 h-5" />
                    </div>
                    <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded-full
                        {{ $p['color'] === 'brand-clay' ? 'bg-brand-clay/10 text-brand-clay' : 'bg-brand-canopy/10 text-brand-canopy' }}">
                        {{ $p['formato'] }}
                    </span>
                </div>
                <div class="flex-1">
                    <h3 class="font-serif font-semibold text-brand-canopy text-sm mb-1">{{ $p['titulo'] }}</h3>
                    <p class="text-xs text-ink-soft leading-relaxed">{{ $p['desc'] }}</p>
                </div>
                <button
                    x-on:click="showToast('Reporte agregándose a cola...')"
                    class="btn-primary text-xs py-2 w-full mt-auto"
                >Generar</button>
            </div>
            @endforeach

        </div>
    </div>

    {{-- Tab: Reportes generados --}}
    <div x-show="activeTab === 'generados'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" style="display:none;">

        @php
        $reportes = [
            ['id' => 1,  'nombre' => 'Ejecutivo — semana 20',       'plantilla' => 'Reporte ejecutivo semanal', 'usuario' => 'Administrador',  'fecha' => '21/05/2026', 'formato' => 'PDF'],
            ['id' => 2,  'nombre' => 'Obras — mayo 2026',           'plantilla' => 'Análisis por tema',         'usuario' => 'Analista',       'fecha' => '20/05/2026', 'formato' => 'PDF'],
            ['id' => 3,  'nombre' => 'Export completo 2do bimestre','plantilla' => 'Export datos brutos',       'usuario' => 'Administrador',  'fecha' => '18/05/2026', 'formato' => 'Excel'],
            ['id' => 4,  'nombre' => 'Tendencia abril-mayo',        'plantilla' => 'Tendencia mensual',         'usuario' => 'Analista',       'fecha' => '15/05/2026', 'formato' => 'PDF'],
            ['id' => 5,  'nombre' => 'Facebook vs Formulario',      'plantilla' => 'Reporte de canales',        'usuario' => 'Administrador',  'fecha' => '12/05/2026', 'formato' => 'PDF'],
            ['id' => 6,  'nombre' => 'Top quejas semana 19',        'plantilla' => 'Top quejas / elogios',      'usuario' => 'Analista',       'fecha' => '10/05/2026', 'formato' => 'PDF'],
            ['id' => 7,  'nombre' => 'Ejecutivo — semana 18',       'plantilla' => 'Reporte ejecutivo semanal', 'usuario' => 'Administrador',  'fecha' => '06/05/2026', 'formato' => 'PDF'],
            ['id' => 8,  'nombre' => 'Seguridad ciudadana abril',   'plantilla' => 'Análisis por tema',         'usuario' => 'Analista',       'fecha' => '03/05/2026', 'formato' => 'PDF'],
            ['id' => 9,  'nombre' => 'Export datos abril',          'plantilla' => 'Export datos brutos',       'usuario' => 'Administrador',  'fecha' => '30/04/2026', 'formato' => 'Excel'],
            ['id' => 10, 'nombre' => 'Tendencia marzo-abril',       'plantilla' => 'Tendencia mensual',         'usuario' => 'Analista',       'fecha' => '25/04/2026', 'formato' => 'PDF'],
        ];
        @endphp

        @if (count($reportes) === 0)
            <div class="card text-center py-14">
                <x-icon name="file-text" class="w-10 h-10 text-ink-soft mx-auto mb-3" />
                <p class="text-ink-soft text-sm">No hay reportes generados aún.</p>
                <button x-on:click="modalOpen = true" class="mt-4 btn-primary text-sm">Crear primer reporte</button>
            </div>
        @else
            <div class="card overflow-hidden p-0">
                <table class="w-full text-sm">
                    <thead class="bg-brand-mist border-b border-gray-100">
                        <tr>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-ink-soft uppercase tracking-wide w-8">#</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-ink-soft uppercase tracking-wide">Nombre</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-ink-soft uppercase tracking-wide hidden lg:table-cell">Plantilla</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-ink-soft uppercase tracking-wide hidden md:table-cell">Generado por</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-ink-soft uppercase tracking-wide">Fecha</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-ink-soft uppercase tracking-wide">Formato</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-ink-soft uppercase tracking-wide">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($reportes as $r)
                        <tr class="hover:bg-brand-mist/50 transition-colors">
                            <td class="px-4 py-3 text-ink-soft text-xs">{{ $r['id'] }}</td>
                            <td class="px-4 py-3">
                                <span class="font-medium text-ink-deep">{{ $r['nombre'] }}</span>
                            </td>
                            <td class="px-4 py-3 text-ink-soft text-xs hidden lg:table-cell">{{ $r['plantilla'] }}</td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full
                                    {{ $r['usuario'] === 'Administrador' ? 'bg-brand-canopy/10 text-brand-canopy' : 'bg-brand-river/10 text-brand-river' }}">
                                    {{ $r['usuario'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-xs text-ink-soft">{{ $r['fecha'] }}</td>
                            <td class="px-4 py-3">
                                <span class="text-[10px] font-bold uppercase tracking-wide px-2 py-0.5 rounded-full
                                    {{ $r['formato'] === 'Excel' ? 'bg-brand-canopy/10 text-brand-canopy' : 'bg-brand-clay/10 text-brand-clay' }}">
                                    {{ $r['formato'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <button
                                        x-on:click="showToast('Descargando {{ addslashes($r['nombre']) }}...')"
                                        title="Descargar"
                                        class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-brand-canopy/10 text-brand-canopy transition-colors"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    </button>
                                    <button
                                        x-on:click="showToast('Reporte eliminado.')"
                                        title="Eliminar"
                                        class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-brand-clay/10 text-brand-clay transition-colors"
                                    >
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

    </div>

</div>
