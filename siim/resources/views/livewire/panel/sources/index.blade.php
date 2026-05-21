<?php

use function Livewire\Volt\title;
use function Livewire\Volt\state;

title('Fuentes de ingesta · SIIM');

state([
    'flash' => null,
]);

?>

<div
    x-data="{
        open: null,
        flash: null,
        showFlash(msg) {
            this.flash = msg;
            setTimeout(() => this.flash = null, 3000);
        },
        executeAll() {
            this.showFlash('Todas las fuentes ejecutadas correctamente.');
        },
        execute(name) {
            this.showFlash('Fuente «' + name + '» ejecutada.');
        },
        saveConfig(name) {
            this.open = null;
            this.showFlash('Configuración de «' + name + '» guardada.');
        }
    }"
    class="space-y-8"
>
    {{-- Flash --}}
    <div
        x-show="flash"
        x-transition
        class="fixed top-6 right-6 z-50 bg-brand-canopy text-white text-sm px-5 py-3 rounded-xl shadow-brand-lg flex items-center gap-2"
    >
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
        <span x-text="flash"></span>
    </div>

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="font-serif text-2xl font-bold text-brand-canopy">Fuentes de ingesta</h1>
            <p class="mt-1 text-sm text-ink-soft">Conecta y gestiona los canales por donde llegan los comentarios ciudadanos.</p>
        </div>
        <button
            @click="executeAll()"
            class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow transition-colors"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Ejecutar todas
        </button>
    </div>

    {{-- KPI Stats row --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ([
            ['label' => 'Comentarios totales hoy',  'value' => '47',        'icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z', 'color' => 'text-brand-canopy'],
            ['label' => 'Última ingesta',            'value' => 'hace 12 min','icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',                                                                    'color' => 'text-brand-river'],
            ['label' => 'Tasa éxito 30 días',        'value' => '98.4 %',    'icon' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z', 'color' => 'text-emerald-600'],
            ['label' => 'Errores activos',           'value' => '0',         'icon' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z', 'color' => 'text-ink-soft'],
        ] as $kpi)
        <div class="card flex items-center gap-4 py-5">
            <div class="w-10 h-10 rounded-xl bg-brand-mist flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 {{ $kpi['color'] }}" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $kpi['icon'] }}"/>
                </svg>
            </div>
            <div>
                <div class="text-xs text-ink-soft">{{ $kpi['label'] }}</div>
                <div class="text-xl font-bold {{ $kpi['color'] }} font-serif leading-tight">{{ $kpi['value'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Sources grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- 1. Meta Graph API --}}
        <div class="card flex flex-col gap-0 p-0 overflow-hidden">
            <div class="flex items-start gap-4 p-5 border-b border-brand-canopy/8">
                <div class="w-12 h-12 rounded-2xl bg-brand-river/10 flex items-center justify-center shrink-0">
                    <svg class="w-7 h-7 text-brand-river" fill="currentColor" viewBox="0 0 24 24"><path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878V14.89h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="font-serif font-semibold text-ink-deep">Meta Graph API</h3>
                        <span class="inline-flex items-center gap-1 text-xs font-medium bg-emerald-100 text-emerald-700 px-2.5 py-0.5 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Activo
                        </span>
                    </div>
                    <p class="text-xs text-ink-soft mt-0.5">Facebook + Instagram</p>
                </div>
            </div>
            <div class="px-5 py-4 text-sm text-ink-soft">
                Sincronización automática de comentarios en publicaciones oficiales de la municipalidad.
            </div>
            <div class="px-5 pb-4 grid grid-cols-3 gap-3">
                <div class="text-center">
                    <div class="text-lg font-bold text-brand-canopy font-serif">234</div>
                    <div class="text-xs text-ink-soft">últimas 24h</div>
                </div>
                <div class="text-center border-x border-brand-canopy/8">
                    <div class="text-lg font-bold text-emerald-600 font-serif">99.1%</div>
                    <div class="text-xs text-ink-soft">tasa éxito</div>
                </div>
                <div class="text-center">
                    <div class="text-lg font-bold text-ink-soft font-serif">8 min</div>
                    <div class="text-xs text-ink-soft">último run</div>
                </div>
            </div>
            <div class="px-5 pb-5 flex gap-2">
                <button @click="open = 'meta'" class="flex-1 text-sm border border-brand-canopy/20 text-brand-canopy hover:bg-brand-canopy/5 rounded-xl py-2 font-medium transition-colors">Configurar</button>
                <button @click="execute('Meta Graph API')" class="flex-1 text-sm bg-brand-canopy text-white hover:bg-brand-canopy/90 rounded-xl py-2 font-medium transition-colors">Ejecutar ahora</button>
            </div>
        </div>

        {{-- 2. Buzón ciudadano --}}
        <div class="card flex flex-col gap-0 p-0 overflow-hidden">
            <div class="flex items-start gap-4 p-5 border-b border-brand-canopy/8">
                <div class="w-12 h-12 rounded-2xl bg-brand-gold/10 flex items-center justify-center shrink-0">
                    <svg class="w-7 h-7 text-brand-gold" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="font-serif font-semibold text-ink-deep">Formulario web público</h3>
                        <span class="inline-flex items-center gap-1 text-xs font-medium bg-emerald-100 text-emerald-700 px-2.5 py-0.5 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Activo
                        </span>
                    </div>
                    <p class="text-xs text-ink-soft mt-0.5">Buzón ciudadano</p>
                </div>
            </div>
            <div class="px-5 py-4 text-sm text-ink-soft">
                Recibe sugerencias y reclamos desde la página <code class="text-brand-river bg-brand-river/8 px-1 rounded">/buzon</code> con Cloudflare Turnstile anti-spam.
            </div>
            <div class="px-5 pb-4 grid grid-cols-3 gap-3">
                <div class="text-center">
                    <div class="text-lg font-bold text-brand-canopy font-serif">18</div>
                    <div class="text-xs text-ink-soft">últimas 24h</div>
                </div>
                <div class="text-center border-x border-brand-canopy/8">
                    <div class="text-lg font-bold text-emerald-600 font-serif">100%</div>
                    <div class="text-xs text-ink-soft">tasa éxito</div>
                </div>
                <div class="text-center">
                    <div class="text-lg font-bold text-ink-soft font-serif">2 min</div>
                    <div class="text-xs text-ink-soft">último run</div>
                </div>
            </div>
            <div class="px-5 pb-5 flex gap-2">
                <button @click="open = 'web_form'" class="flex-1 text-sm border border-brand-canopy/20 text-brand-canopy hover:bg-brand-canopy/5 rounded-xl py-2 font-medium transition-colors">Configurar</button>
                <button @click="execute('Buzón ciudadano')" class="flex-1 text-sm bg-brand-canopy text-white hover:bg-brand-canopy/90 rounded-xl py-2 font-medium transition-colors">Ejecutar ahora</button>
            </div>
        </div>

        {{-- 3. CSV/Excel --}}
        <div class="card flex flex-col gap-0 p-0 overflow-hidden">
            <div class="flex items-start gap-4 p-5 border-b border-brand-canopy/8">
                <div class="w-12 h-12 rounded-2xl bg-brand-mist flex items-center justify-center shrink-0">
                    <svg class="w-7 h-7 text-ink-soft" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="font-serif font-semibold text-ink-deep">Importación CSV / Excel</h3>
                        <span class="inline-flex items-center gap-1 text-xs font-medium bg-gray-100 text-gray-500 px-2.5 py-0.5 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> En espera
                        </span>
                    </div>
                    <p class="text-xs text-ink-soft mt-0.5">Carga masiva manual</p>
                </div>
            </div>
            <div class="px-5 py-4 text-sm text-ink-soft">
                Sube archivos masivos (.csv .xlsx) con comentarios recolectados manualmente.
            </div>
            <div class="px-5 pb-4 grid grid-cols-3 gap-3">
                <div class="text-center">
                    <div class="text-lg font-bold text-brand-canopy font-serif">0</div>
                    <div class="text-xs text-ink-soft">hoy</div>
                </div>
                <div class="text-center border-x border-brand-canopy/8">
                    <div class="text-lg font-bold text-ink-soft font-serif">3 días</div>
                    <div class="text-xs text-ink-soft">última import.</div>
                </div>
                <div class="text-center">
                    <div class="text-lg font-bold text-brand-river font-serif">487</div>
                    <div class="text-xs text-ink-soft">registros</div>
                </div>
            </div>
            <div class="px-5 pb-5 flex gap-2">
                <button @click="open = 'csv'" class="flex-1 text-sm border border-brand-canopy/20 text-brand-canopy hover:bg-brand-canopy/5 rounded-xl py-2 font-medium transition-colors">Configurar</button>
                <button @click="execute('CSV/Excel')" class="flex-1 text-sm bg-brand-canopy text-white hover:bg-brand-canopy/90 rounded-xl py-2 font-medium transition-colors">Ejecutar ahora</button>
            </div>
        </div>

        {{-- 4. Chatbot --}}
        <div class="card flex flex-col gap-0 p-0 overflow-hidden">
            <div class="flex items-start gap-4 p-5 border-b border-brand-canopy/8">
                <div class="w-12 h-12 rounded-2xl bg-brand-canopy/10 flex items-center justify-center shrink-0">
                    <svg class="w-7 h-7 text-brand-canopy" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <h3 class="font-serif font-semibold text-ink-deep">Chatbot público</h3>
                        <span class="inline-flex items-center gap-1 text-xs font-medium bg-emerald-100 text-emerald-700 px-2.5 py-0.5 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Activo
                        </span>
                    </div>
                    <p class="text-xs text-ink-soft mt-0.5">Asistente ciudadano IA</p>
                </div>
            </div>
            <div class="px-5 py-4 text-sm text-ink-soft">
                Captura las quejas y sugerencias detectadas en conversaciones con el chatbot ciudadano.
            </div>
            <div class="px-5 pb-4 grid grid-cols-3 gap-3">
                <div class="text-center">
                    <div class="text-lg font-bold text-brand-canopy font-serif">12</div>
                    <div class="text-xs text-ink-soft">últimas 24h</div>
                </div>
                <div class="text-center border-x border-brand-canopy/8">
                    <div class="text-lg font-bold text-emerald-600 font-serif">96.5%</div>
                    <div class="text-xs text-ink-soft">tasa éxito</div>
                </div>
                <div class="text-center">
                    <div class="text-lg font-bold text-ink-soft font-serif">15 min</div>
                    <div class="text-xs text-ink-soft">último run</div>
                </div>
            </div>
            <div class="px-5 pb-5 flex gap-2">
                <button @click="open = 'chatbot'" class="flex-1 text-sm border border-brand-canopy/20 text-brand-canopy hover:bg-brand-canopy/5 rounded-xl py-2 font-medium transition-colors">Configurar</button>
                <button @click="execute('Chatbot público')" class="flex-1 text-sm bg-brand-canopy text-white hover:bg-brand-canopy/90 rounded-xl py-2 font-medium transition-colors">Ejecutar ahora</button>
            </div>
        </div>

    </div>{{-- /grid --}}

    {{-- Histórico de ingestas --}}
    <div class="card">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-8 h-8 rounded-lg bg-brand-canopy/10 flex items-center justify-center">
                <x-icon name="history" class="w-4 h-4 text-brand-canopy" />
            </div>
            <h2 class="font-serif font-semibold text-ink-deep">Histórico de ingestas</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-brand-canopy/10 text-xs text-ink-soft uppercase tracking-wide">
                        <th class="pb-3 text-left font-medium w-8">#</th>
                        <th class="pb-3 text-left font-medium">Fuente</th>
                        <th class="pb-3 text-left font-medium">Inicio</th>
                        <th class="pb-3 text-left font-medium">Duración</th>
                        <th class="pb-3 text-right font-medium">Aceptados</th>
                        <th class="pb-3 text-right font-medium">Rechazados</th>
                        <th class="pb-3 text-center font-medium">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-canopy/6">
                    @foreach ([
                        [10, 'Meta Graph API',  '21/05 14:42', '8s',  '41',  '0',  'success'],
                        [ 9, 'Chatbot público', '21/05 14:30', '3s',  '12',  '0',  'success'],
                        [ 8, 'Meta Graph API',  '21/05 14:12', '9s',  '38',  '1',  'success'],
                        [ 7, 'Buzón ciudadano', '21/05 14:00', '2s',  '7',   '0',  'success'],
                        [ 6, 'Meta Graph API',  '21/05 13:42', '11s', '29',  '3',  'partial'],
                        [ 5, 'Chatbot público', '21/05 13:15', '4s',  '8',   '0',  'success'],
                        [ 4, 'Meta Graph API',  '21/05 13:12', '7s',  '31',  '0',  'success'],
                        [ 3, 'CSV / Excel',     '21/05 11:30', '45s', '487', '13', 'success'],
                        [ 2, 'Buzón ciudadano', '21/05 10:00', '2s',  '4',   '0',  'success'],
                        [ 1, 'Meta Graph API',  '21/05 09:42', '6s',  '22',  '0',  'failed'],
                    ] as $run)
                    <tr class="hover:bg-brand-mist/40 transition-colors">
                        <td class="py-3 text-ink-soft">{{ $run[0] }}</td>
                        <td class="py-3 font-medium text-ink-deep">{{ $run[1] }}</td>
                        <td class="py-3 text-ink-soft">{{ $run[2] }}</td>
                        <td class="py-3 text-ink-soft">{{ $run[3] }}</td>
                        <td class="py-3 text-right font-medium text-emerald-700">{{ $run[4] }}</td>
                        <td class="py-3 text-right text-ink-soft">{{ $run[5] }}</td>
                        <td class="py-3 text-center">
                            @if($run[6] === 'success')
                                <span class="inline-block text-xs font-medium bg-emerald-100 text-emerald-700 px-2.5 py-0.5 rounded-full">Exitoso</span>
                            @elseif($run[6] === 'partial')
                                <span class="inline-block text-xs font-medium bg-amber-100 text-amber-700 px-2.5 py-0.5 rounded-full">Parcial</span>
                            @else
                                <span class="inline-block text-xs font-medium bg-red-100 text-red-700 px-2.5 py-0.5 rounded-full">Fallido</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- SLIDE-OVER backdrop --}}
    <div
        x-show="open !== null"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = null"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40"
    ></div>

    {{-- SLIDE-OVER panel --}}
    <div
        x-show="open !== null"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed inset-y-0 right-0 w-full max-w-md bg-white shadow-brand-lg z-50 flex flex-col"
    >
        {{-- Slide-over header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-brand-canopy/10 bg-brand-mist/40">
            <h3 class="font-serif font-semibold text-ink-deep">
                <span x-show="open === 'meta'">Meta Graph API — Configurar</span>
                <span x-show="open === 'web_form'">Buzón ciudadano — Configurar</span>
                <span x-show="open === 'csv'">CSV / Excel — Configurar</span>
                <span x-show="open === 'chatbot'">Chatbot público — Configurar</span>
            </h3>
            <button @click="open = null" class="w-8 h-8 rounded-lg hover:bg-brand-canopy/10 flex items-center justify-center text-ink-soft transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        {{-- Slide-over body --}}
        <div class="flex-1 overflow-y-auto px-6 py-6 space-y-5">

            {{-- META config --}}
            <div x-show="open === 'meta'" class="space-y-5">
                <div>
                    <label class="block text-xs font-medium text-ink-soft mb-1.5">Token Meta (encriptado)</label>
                    <input type="password" value="EAAGm0PaB..." class="w-full rounded-xl border border-brand-canopy/15 bg-brand-mist/30 px-3 py-2.5 text-sm focus:border-brand-canopy focus:outline-none focus:ring-1 focus:ring-brand-canopy/20"/>
                </div>
                <div>
                    <label class="block text-xs font-medium text-ink-soft mb-1.5">Página Facebook ID</label>
                    <input type="text" value="107523451234567" class="w-full rounded-xl border border-brand-canopy/15 bg-brand-mist/30 px-3 py-2.5 text-sm focus:border-brand-canopy focus:outline-none focus:ring-1 focus:ring-brand-canopy/20"/>
                </div>
                <div>
                    <label class="block text-xs font-medium text-ink-soft mb-1.5">Instagram Business ID</label>
                    <input type="text" value="17841451234567" class="w-full rounded-xl border border-brand-canopy/15 bg-brand-mist/30 px-3 py-2.5 text-sm focus:border-brand-canopy focus:outline-none focus:ring-1 focus:ring-brand-canopy/20"/>
                </div>
                <div>
                    <label class="block text-xs font-medium text-ink-soft mb-1.5">Frecuencia de sincronización</label>
                    <select class="w-full rounded-xl border border-brand-canopy/15 bg-brand-mist/30 px-3 py-2.5 text-sm focus:border-brand-canopy focus:outline-none">
                        <option>Cada 15 minutos</option>
                        <option selected>Cada 30 minutos</option>
                        <option>Cada 60 minutos</option>
                    </select>
                </div>
                <div class="flex items-center justify-between py-2">
                    <span class="text-sm font-medium text-ink-deep">Habilitado</span>
                    <button class="relative inline-flex h-6 w-11 items-center rounded-full bg-brand-canopy transition-colors">
                        <span class="translate-x-6 inline-block h-4 w-4 rounded-full bg-white shadow transition-transform"></span>
                    </button>
                </div>
            </div>

            {{-- WEB FORM config --}}
            <div x-show="open === 'web_form'" class="space-y-5">
                <div>
                    <label class="block text-xs font-medium text-ink-soft mb-1.5">Mensaje de bienvenida</label>
                    <textarea rows="3" class="w-full rounded-xl border border-brand-canopy/15 bg-brand-mist/30 px-3 py-2.5 text-sm focus:border-brand-canopy focus:outline-none focus:ring-1 focus:ring-brand-canopy/20">Comparte tu opinión con la Municipalidad Distrital de San Ramón. Tu comentario nos ayuda a mejorar.</textarea>
                </div>
                <div class="flex items-center justify-between py-2">
                    <span class="text-sm font-medium text-ink-deep">Habilitar campo contacto opcional</span>
                    <button class="relative inline-flex h-6 w-11 items-center rounded-full bg-brand-canopy transition-colors">
                        <span class="translate-x-6 inline-block h-4 w-4 rounded-full bg-white shadow transition-transform"></span>
                    </button>
                </div>
                <div class="flex items-center justify-between py-2">
                    <span class="text-sm font-medium text-ink-deep">Notificar email al admin</span>
                    <button class="relative inline-flex h-6 w-11 items-center rounded-full bg-brand-canopy transition-colors">
                        <span class="translate-x-6 inline-block h-4 w-4 rounded-full bg-white shadow transition-transform"></span>
                    </button>
                </div>
                <div>
                    <label class="block text-xs font-medium text-ink-soft mb-1.5">Rate limit por IP</label>
                    <div class="flex items-center gap-2">
                        <input type="number" value="5" class="w-24 rounded-xl border border-brand-canopy/15 bg-brand-mist/30 px-3 py-2.5 text-sm focus:border-brand-canopy focus:outline-none"/>
                        <span class="text-sm text-ink-soft">solicitudes / minuto</span>
                    </div>
                </div>
            </div>

            {{-- CSV config --}}
            <div x-show="open === 'csv'" class="space-y-5">
                <div
                    class="border-2 border-dashed border-brand-canopy/25 rounded-2xl p-8 text-center hover:border-brand-canopy/50 hover:bg-brand-mist/40 transition-colors cursor-pointer"
                    @dragover.prevent
                    @drop.prevent
                >
                    <svg class="w-10 h-10 mx-auto text-ink-soft mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    <p class="text-sm font-medium text-ink-deep">Arrastra tu archivo aquí</p>
                    <p class="text-xs text-ink-soft mt-1">o haz clic para seleccionar</p>
                    <p class="text-xs text-ink-soft mt-3 font-mono bg-brand-mist px-3 py-1.5 rounded-lg inline-block">.csv · .xlsx — máx. 10 MB</p>
                </div>
                <div class="bg-brand-mist/60 rounded-xl px-4 py-3">
                    <p class="text-xs font-medium text-ink-soft mb-1.5">Columnas esperadas</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach(['text', 'channel?', 'captured_at?', 'author_alias?'] as $col)
                        <span class="font-mono text-xs bg-white border border-brand-canopy/15 text-brand-canopy px-2 py-0.5 rounded-lg">{{ $col }}</span>
                        @endforeach
                    </div>
                </div>
                <button @click="saveConfig('CSV/Excel')" class="w-full btn-primary justify-center">
                    Procesar archivo
                </button>
            </div>

            {{-- CHATBOT config --}}
            <div x-show="open === 'chatbot'" class="space-y-5">
                <div>
                    <label class="block text-xs font-medium text-ink-soft mb-1.5">Mensaje de bienvenida del bot</label>
                    <textarea rows="3" class="w-full rounded-xl border border-brand-canopy/15 bg-brand-mist/30 px-3 py-2.5 text-sm focus:border-brand-canopy focus:outline-none focus:ring-1 focus:ring-brand-canopy/20">Hola, soy el asistente de la Municipalidad de San Ramón. ¿En qué puedo ayudarte?</textarea>
                </div>
                <div>
                    <label class="block text-xs font-medium text-ink-soft mb-1.5">Modelo LLM</label>
                    <select class="w-full rounded-xl border border-brand-canopy/15 bg-brand-mist/30 px-3 py-2.5 text-sm focus:border-brand-canopy focus:outline-none">
                        <option selected>NVIDIA GLM 5.1 (z-ai/glm-5.1)</option>
                        <option>Groq Llama 3.3 70B</option>
                        <option>Google Gemini 2.0 Flash</option>
                    </select>
                </div>
                <div class="flex items-center justify-between py-2">
                    <span class="text-sm font-medium text-ink-deep">Persistir conversaciones completas</span>
                    <button class="relative inline-flex h-6 w-11 items-center rounded-full bg-brand-canopy transition-colors">
                        <span class="translate-x-6 inline-block h-4 w-4 rounded-full bg-white shadow transition-transform"></span>
                    </button>
                </div>
            </div>

        </div>{{-- /body --}}

        {{-- Slide-over footer --}}
        <div class="px-6 py-4 border-t border-brand-canopy/10 bg-brand-mist/40 flex gap-3" x-show="open !== 'csv'">
            <button @click="open = null" class="flex-1 text-sm border border-brand-canopy/20 text-brand-canopy hover:bg-brand-canopy/5 rounded-xl py-2.5 font-medium transition-colors">Cancelar</button>
            <button
                @click="saveConfig(open)"
                class="flex-1 btn-primary justify-center text-sm"
            >Guardar cambios</button>
        </div>
    </div>

</div>
