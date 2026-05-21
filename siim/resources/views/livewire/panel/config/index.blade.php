<?php

use function Livewire\Volt\title;
use function Livewire\Volt\state;

title('Configuración · SIIM');

state([
    'tab' => 'llm',
]);

?>

<div
    x-data="{
        tab: 'llm',
        modal: null,
        flash: null,
        showFlash(msg) {
            this.flash = msg;
            setTimeout(() => this.flash = null, 3000);
        },
        save(section) {
            this.showFlash('Cambios en «' + section + '» guardados correctamente.');
        }
    }"
    class="space-y-6"
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
    <div>
        <h1 class="font-serif text-2xl font-bold text-brand-canopy">Configuración del sistema</h1>
        <p class="mt-1 text-sm text-ink-soft">Ajustes globales SIIM — solo administradores.</p>
    </div>

    {{-- Layout: tab sidebar + content --}}
    <div class="flex flex-col lg:flex-row gap-6">

        {{-- Sidebar tabs --}}
        <nav class="lg:w-52 shrink-0 flex lg:flex-col gap-1">
            @foreach ([
                ['id' => 'llm',       'label' => 'Proveedor LLM',      'icon' => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
                ['id' => 'budget',    'label' => 'Presupuesto',         'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['id' => 'prompts',   'label' => 'Prompts versionados', 'icon' => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4'],
                ['id' => 'topics',    'label' => 'Vocabulario temas',   'icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z'],
                ['id' => 'notif',     'label' => 'Notificaciones',      'icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
            ] as $t)
            <button
                @click="tab = '{{ $t['id'] }}'"
                :class="tab === '{{ $t['id'] }}' ? 'bg-brand-canopy text-white shadow-brand' : 'text-ink-soft hover:bg-brand-mist hover:text-ink-deep'"
                class="flex items-center gap-2.5 w-full px-4 py-2.5 rounded-xl text-sm font-medium transition-all text-left"
            >
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $t['icon'] }}"/>
                </svg>
                {{ $t['label'] }}
            </button>
            @endforeach
        </nav>

        {{-- Content panels --}}
        <div class="flex-1 min-w-0 space-y-6">

            {{-- === LLM PROVIDER === --}}
            <div x-show="tab === 'llm'" class="space-y-5">

                {{-- Active provider card --}}
                <div class="card bg-gradient-to-br from-brand-canopy/5 to-brand-river/5 border border-brand-canopy/10">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-9 h-9 rounded-xl bg-brand-canopy flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <p class="font-serif font-semibold text-ink-deep">Proveedor activo</p>
                            <p class="text-xs text-ink-soft">En producción ahora mismo</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                        <div><div class="text-xs text-ink-soft">Proveedor</div><div class="font-semibold text-ink-deep text-sm mt-0.5">NVIDIA GLM 5.1</div></div>
                        <div><div class="text-xs text-ink-soft">Modelo</div><div class="font-mono text-xs text-brand-river mt-0.5">z-ai/glm-5.1</div></div>
                        <div><div class="text-xs text-ink-soft">Latencia promedio</div><div class="font-semibold text-ink-deep text-sm mt-0.5">4.2 s</div></div>
                        <div><div class="text-xs text-ink-soft">Tasa éxito</div><div class="font-semibold text-emerald-600 text-sm mt-0.5">99.7%</div></div>
                    </div>
                </div>

                {{-- Providers table --}}
                <div class="card">
                    <h3 class="font-serif font-semibold text-ink-deep mb-4">Proveedores disponibles</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-brand-canopy/10 text-xs text-ink-soft uppercase tracking-wide">
                                    <th class="pb-3 text-left font-medium">Proveedor</th>
                                    <th class="pb-3 text-left font-medium">Modelo</th>
                                    <th class="pb-3 text-center font-medium">Estado</th>
                                    <th class="pb-3 text-right font-medium">Costo / 1M tokens</th>
                                    <th class="pb-3 text-center font-medium">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-brand-canopy/6">
                                @foreach ([
                                    ['NVIDIA GLM 5.1',           'z-ai/glm-5.1',            true,  'Gratis (créditos)'],
                                    ['OpenAI GPT-4o mini',       'gpt-4o-mini',             false, '$ 0.60 / $ 2.40'],
                                    ['Anthropic Claude Haiku',   'claude-3-haiku-20240307', false, '$ 0.25 / $ 1.25'],
                                    ['Google Gemini 2.0 Flash',  'gemini-2.0-flash',        false, '$ 0.10 / $ 0.40'],
                                    ['Groq Llama 3.3 70B',       'llama-3.3-70b-versatile', false, 'Gratis (tier free)'],
                                ] as $provider)
                                <tr class="hover:bg-brand-mist/40 transition-colors {{ $provider[2] ? 'bg-brand-canopy/3' : '' }}">
                                    <td class="py-3 font-medium text-ink-deep">{{ $provider[0] }}</td>
                                    <td class="py-3 font-mono text-xs text-ink-soft">{{ $provider[1] }}</td>
                                    <td class="py-3 text-center">
                                        @if($provider[2])
                                            <span class="inline-flex items-center gap-1 text-xs font-medium bg-emerald-100 text-emerald-700 px-2.5 py-0.5 rounded-full">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Activo
                                            </span>
                                        @else
                                            <span class="inline-block text-xs text-ink-soft px-2.5 py-0.5 rounded-full bg-gray-100">Inactivo</span>
                                        @endif
                                    </td>
                                    <td class="py-3 text-right text-ink-soft text-xs">{{ $provider[3] }}</td>
                                    <td class="py-3 text-center">
                                        @if(!$provider[2])
                                        <button class="text-xs border border-brand-canopy/25 text-brand-canopy hover:bg-brand-canopy hover:text-white px-3 py-1 rounded-lg transition-colors">Activar</button>
                                        @else
                                        <span class="text-xs text-ink-soft">—</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Fallback toggle --}}
                    <div class="mt-5 pt-5 border-t border-brand-canopy/8 space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-ink-deep">Habilitar fallback automático</p>
                                <p class="text-xs text-ink-soft mt-0.5">Cambia al proveedor alternativo si el principal falla</p>
                            </div>
                            <button class="relative inline-flex h-6 w-11 items-center rounded-full bg-brand-canopy transition-colors">
                                <span class="translate-x-6 inline-block h-4 w-4 rounded-full bg-white shadow transition-transform"></span>
                            </button>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-ink-soft mb-1.5">Proveedor de fallback</label>
                            <select class="w-full rounded-xl border border-brand-canopy/15 bg-brand-mist/30 px-3 py-2.5 text-sm focus:border-brand-canopy focus:outline-none">
                                <option>Groq Llama 3.3 70B</option>
                                <option>Google Gemini 2.0 Flash</option>
                                <option>OpenAI GPT-4o mini</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button @click="save('Proveedor LLM')" class="btn-primary">Guardar cambios</button>
                </div>
            </div>

            {{-- === BUDGET === --}}
            <div x-show="tab === 'budget'" class="space-y-5">
                <div class="card space-y-5">
                    <h3 class="font-serif font-semibold text-ink-deep">Presupuesto y límites</h3>

                    <div>
                        <label class="block text-xs font-medium text-ink-soft mb-1.5">Presupuesto diario (USD)</label>
                        <div class="flex items-center gap-2">
                            <span class="text-sm text-ink-soft font-mono">$</span>
                            <input type="number" step="0.5" value="5.00" class="w-36 rounded-xl border border-brand-canopy/15 bg-brand-mist/30 px-3 py-2.5 text-sm focus:border-brand-canopy focus:outline-none focus:ring-1 focus:ring-brand-canopy/20"/>
                        </div>
                    </div>

                    {{-- Progress bar --}}
                    <div>
                        <div class="flex items-center justify-between text-xs mb-2">
                            <span class="text-ink-soft">Gasto hoy</span>
                            <span class="font-semibold text-ink-deep">$ 1.23 / $ 5.00</span>
                        </div>
                        <div class="w-full bg-brand-mist rounded-full h-3">
                            <div class="bg-brand-river h-3 rounded-full transition-all" style="width: 24.6%"></div>
                        </div>
                        <p class="text-xs text-ink-soft mt-1.5">24.6% del presupuesto diario utilizado</p>
                    </div>

                    {{-- Last 7 days table --}}
                    <div>
                        <h4 class="text-sm font-medium text-ink-deep mb-3">Últimos 7 días</h4>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-brand-canopy/10 text-xs text-ink-soft uppercase tracking-wide">
                                        <th class="pb-2 text-left font-medium">Fecha</th>
                                        <th class="pb-2 text-right font-medium">Tokens in</th>
                                        <th class="pb-2 text-right font-medium">Tokens out</th>
                                        <th class="pb-2 text-right font-medium">Costo USD</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-brand-canopy/6">
                                    @foreach ([
                                        ['21 may', '48,200',  '12,300', '$ 1.23'],
                                        ['20 may', '61,500',  '15,800', '$ 1.54'],
                                        ['19 may', '39,800',  '10,200', '$ 1.01'],
                                        ['18 may', '52,100',  '13,600', '$ 1.31'],
                                        ['17 may', '44,600',  '11,400', '$ 1.13'],
                                        ['16 may', '58,300',  '14,900', '$ 1.46'],
                                        ['15 may', '37,500',  '9,800',  '$ 0.95'],
                                    ] as $row)
                                    <tr class="hover:bg-brand-mist/40 transition-colors">
                                        <td class="py-2 text-ink-soft">{{ $row[0] }}</td>
                                        <td class="py-2 text-right font-mono text-xs text-ink-deep">{{ $row[1] }}</td>
                                        <td class="py-2 text-right font-mono text-xs text-ink-deep">{{ $row[2] }}</td>
                                        <td class="py-2 text-right font-semibold text-brand-canopy">{{ $row[3] }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-brand-canopy/8 space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-ink-deep">Pausar análisis al exceder presupuesto</p>
                                <p class="text-xs text-ink-soft mt-0.5">Detiene análisis automático cuando se alcanza el límite</p>
                            </div>
                            <button class="relative inline-flex h-6 w-11 items-center rounded-full bg-brand-canopy transition-colors">
                                <span class="translate-x-6 inline-block h-4 w-4 rounded-full bg-white shadow transition-transform"></span>
                            </button>
                        </div>
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-ink-deep">Notificar al admin al 80%</p>
                                <p class="text-xs text-ink-soft mt-0.5">Alerta cuando el gasto supere el 80% del presupuesto</p>
                            </div>
                            <button class="relative inline-flex h-6 w-11 items-center rounded-full bg-brand-canopy transition-colors">
                                <span class="translate-x-6 inline-block h-4 w-4 rounded-full bg-white shadow transition-transform"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button @click="save('Presupuesto')" class="btn-primary">Guardar cambios</button>
                </div>
            </div>

            {{-- === PROMPTS === --}}
            <div x-show="tab === 'prompts'" class="space-y-5">
                <div class="card">
                    <h3 class="font-serif font-semibold text-ink-deep mb-4">Prompts del sistema versionados</h3>
                    <div class="space-y-3">
                        @foreach ([
                            ['key' => 'sentiment_analysis',   'label' => 'Análisis de sentimiento',      'version' => 'v3', 'updated' => 'hace 2 días'],
                            ['key' => 'topic_classification',  'label' => 'Clasificación de temas',       'version' => 'v3', 'updated' => 'hace 4 días'],
                            ['key' => 'rag_system',            'label' => 'Sistema RAG',                  'version' => 'v2', 'updated' => 'hace 1 semana'],
                            ['key' => 'assistant_system',      'label' => 'Asistente del sistema',        'version' => 'v3', 'updated' => 'hace 3 días'],
                        ] as $prompt)
                        <div class="flex items-center justify-between gap-4 p-4 rounded-xl border border-brand-canopy/8 hover:bg-brand-mist/30 transition-colors">
                            <div class="flex items-center gap-3 min-w-0">
                                <span class="font-mono text-xs bg-brand-canopy/8 text-brand-canopy px-2.5 py-1 rounded-lg shrink-0">{{ $prompt['version'] }}</span>
                                <div class="min-w-0">
                                    <p class="font-medium text-ink-deep text-sm">{{ $prompt['label'] }}</p>
                                    <p class="font-mono text-xs text-ink-soft truncate">{{ $prompt['key'] }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-xs text-ink-soft hidden sm:block">{{ $prompt['updated'] }}</span>
                                <button @click="modal = '{{ $prompt['key'] }}'" class="text-xs border border-brand-canopy/20 text-brand-canopy hover:bg-brand-canopy/5 px-2.5 py-1 rounded-lg transition-colors">Ver</button>
                                <button @click="modal = '{{ $prompt['key'] }}'" class="text-xs bg-brand-canopy text-white hover:bg-brand-canopy/90 px-2.5 py-1 rounded-lg transition-colors">Editar</button>
                                <button class="text-xs border border-gray-200 text-ink-soft hover:bg-brand-mist px-2.5 py-1 rounded-lg transition-colors">Histórico</button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- === TOPICS === --}}
            <div x-show="tab === 'topics'" class="space-y-5">
                <div class="card">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="font-serif font-semibold text-ink-deep">Vocabulario de temas</h3>
                        <a href="/panel/temas" class="inline-flex items-center gap-1.5 text-sm text-brand-river hover:text-brand-canopy font-medium transition-colors">
                            Ir al editor de temas
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </a>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-5">
                        <div class="text-center p-4 rounded-xl bg-brand-mist/60">
                            <div class="text-2xl font-bold text-brand-canopy font-serif">8</div>
                            <div class="text-xs text-ink-soft mt-0.5">temas activos</div>
                        </div>
                        <div class="text-center p-4 rounded-xl bg-brand-mist/60">
                            <div class="text-2xl font-bold text-brand-river font-serif">24</div>
                            <div class="text-xs text-ink-soft mt-0.5">sub-temas</div>
                        </div>
                        <div class="text-center p-4 rounded-xl bg-brand-mist/60">
                            <div class="text-sm font-semibold text-ink-soft font-serif">hace 3 días</div>
                            <div class="text-xs text-ink-soft mt-0.5">última modificación</div>
                        </div>
                    </div>
                    <p class="text-sm text-ink-soft">El vocabulario de temas define la taxonomía usada por el modelo de clasificación. Gestiona temas, sub-temas y palabras clave desde el editor dedicado.</p>
                </div>
            </div>

            {{-- === NOTIFICATIONS === --}}
            <div x-show="tab === 'notif'" class="space-y-5">
                <div class="card space-y-5">
                    <h3 class="font-serif font-semibold text-ink-deep">Canales de notificación</h3>

                    {{-- Telegram --}}
                    <div class="p-4 rounded-xl border border-brand-canopy/10 space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-brand-river/10 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-brand-river" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.248-2.04 9.607c-.153.683-.554.85-1.122.528l-3.104-2.288-1.498 1.44c-.165.166-.305.305-.627.305l.224-3.174 5.784-5.225c.252-.224-.054-.348-.39-.124L7.6 14.47l-3.036-.95c-.66-.206-.673-.66.138-.977l11.858-4.573c.549-.2 1.03.134.002 1.278z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-ink-deep">Alertas Telegram</p>
                                    <p class="text-xs text-ink-soft">Incidentes críticos</p>
                                </div>
                            </div>
                            <button class="relative inline-flex h-6 w-11 items-center rounded-full bg-brand-canopy transition-colors">
                                <span class="translate-x-6 inline-block h-4 w-4 rounded-full bg-white shadow transition-transform"></span>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-ink-soft mb-1.5">Bot token</label>
                                <input type="password" placeholder="7xxxxxxxxx:AAH..." class="w-full rounded-xl border border-brand-canopy/15 bg-brand-mist/30 px-3 py-2.5 text-sm focus:border-brand-canopy focus:outline-none focus:ring-1 focus:ring-brand-canopy/20"/>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-ink-soft mb-1.5">Chat ID</label>
                                <input type="text" placeholder="-1001234567890" class="w-full rounded-xl border border-brand-canopy/15 bg-brand-mist/30 px-3 py-2.5 text-sm focus:border-brand-canopy focus:outline-none focus:ring-1 focus:ring-brand-canopy/20"/>
                            </div>
                        </div>
                    </div>

                    {{-- Email --}}
                    <div class="p-4 rounded-xl border border-brand-canopy/10">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-brand-gold/10 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-brand-gold" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-ink-deep">Email al administrador</p>
                                    <p class="text-xs text-ink-soft">Solo errores críticos</p>
                                </div>
                            </div>
                            <button class="relative inline-flex h-6 w-11 items-center rounded-full bg-gray-200 transition-colors">
                                <span class="translate-x-1 inline-block h-4 w-4 rounded-full bg-white shadow transition-transform"></span>
                            </button>
                        </div>
                    </div>

                    {{-- Test button --}}
                    <div class="pt-2 border-t border-brand-canopy/8 flex items-center gap-3">
                        <button
                            @click="showFlash('Notificación de prueba enviada.')"
                            class="inline-flex items-center gap-2 border border-brand-canopy/25 text-brand-canopy hover:bg-brand-canopy hover:text-white text-sm font-medium px-4 py-2.5 rounded-xl transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            Enviar notificación de prueba
                        </button>
                        <span class="text-xs text-ink-soft">Verifica que el canal esté correctamente configurado.</span>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button @click="save('Notificaciones')" class="btn-primary">Guardar cambios</button>
                </div>
            </div>

        </div>{{-- /content --}}
    </div>{{-- /layout --}}

    {{-- === MODAL prompts editor === --}}
    <div
        x-show="modal !== null"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click.self="modal = null"
        class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40 flex items-center justify-center p-4"
    >
        <div
            x-show="modal !== null"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="bg-white rounded-2xl shadow-brand-lg w-full max-w-2xl flex flex-col max-h-[85vh]"
        >
            <div class="flex items-center justify-between px-6 py-4 border-b border-brand-canopy/10 bg-brand-mist/40 rounded-t-2xl">
                <h3 class="font-serif font-semibold text-ink-deep">Editar prompt — <span x-text="modal" class="font-mono text-sm text-brand-river"></span></h3>
                <button @click="modal = null" class="w-8 h-8 rounded-lg hover:bg-brand-canopy/10 flex items-center justify-center text-ink-soft transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto px-6 py-5">
                <label class="block text-xs font-medium text-ink-soft mb-2">Contenido del prompt (versión v3 — editar para crear v4)</label>
                <textarea
                    rows="14"
                    class="w-full rounded-xl border border-brand-canopy/15 bg-brand-mist/30 px-4 py-3 text-sm font-mono focus:border-brand-canopy focus:outline-none focus:ring-1 focus:ring-brand-canopy/20 resize-none"
                >Eres un analizador especializado en comentarios ciudadanos para la Municipalidad Distrital de San Ramón.

Tu tarea es analizar el siguiente comentario y determinar:
1. El sentimiento predominante (positivo, negativo, neutro, mixto)
2. La intensidad del sentimiento en escala 0-10
3. Las emociones específicas detectadas
4. Si el comentario contiene una queja, sugerencia, elogio o pregunta

Responde siempre en JSON estructurado. No incluyas texto adicional fuera del JSON.

Idioma de análisis: español peruano. Considera regionalismos locales.</textarea>
                <p class="text-xs text-ink-soft mt-2">Al guardar se creará una nueva versión. La versión anterior quedará en el histórico.</p>
            </div>
            <div class="px-6 py-4 border-t border-brand-canopy/10 bg-brand-mist/40 rounded-b-2xl flex gap-3">
                <button @click="modal = null" class="flex-1 text-sm border border-brand-canopy/20 text-brand-canopy hover:bg-brand-canopy/5 rounded-xl py-2.5 font-medium transition-colors">Cancelar</button>
                <button @click="modal = null; save('Prompts')" class="flex-1 btn-primary justify-center text-sm">Guardar versión nueva</button>
            </div>
        </div>
    </div>

</div>
