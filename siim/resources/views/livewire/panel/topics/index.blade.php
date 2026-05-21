<?php

use function Livewire\Volt\title;
use function Livewire\Volt\state;
use function Livewire\Volt\computed;

title('Temas · SIIM');

state([
    'topics' => [
        ['slug' => 'obras_publicas',  'name' => 'Obras públicas',       'description' => 'Infraestructura, pavimento, plazas, parques y espacios públicos del distrito.',   'parent' => null,  'active' => true,  'comment_count' => 18, 'trend' => 'up',   'sub_count' => 3],
        ['slug' => 'seguridad',       'name' => 'Seguridad ciudadana',  'description' => 'Serenazgo, operativos nocturnos, prevención del delito y emergencias.',            'parent' => null,  'active' => true,  'comment_count' => 12, 'trend' => 'up',   'sub_count' => 2],
        ['slug' => 'limpieza',        'name' => 'Limpieza pública',     'description' => 'Recolección de residuos sólidos, barrido de calles y limpieza de parques.',        'parent' => null,  'active' => true,  'comment_count' => 9,  'trend' => 'down', 'sub_count' => 1],
        ['slug' => 'salud',           'name' => 'Salud',                'description' => 'Campañas de salud preventiva, postas médicas municipales y vacunación.',           'parent' => null,  'active' => true,  'comment_count' => 10, 'trend' => 'flat', 'sub_count' => 2],
        ['slug' => 'tributos',        'name' => 'Tributos',             'description' => 'Impuesto predial, arbitrios, licencias de funcionamiento y pagos municipales.',    'parent' => null,  'active' => true,  'comment_count' => 8,  'trend' => 'flat', 'sub_count' => 1],
        ['slug' => 'educacion',       'name' => 'Educación',            'description' => 'Talleres formativos, becas municipales, biblioteca y programas culturales.',       'parent' => null,  'active' => true,  'comment_count' => 7,  'trend' => 'up',   'sub_count' => 2],
        ['slug' => 'transporte',      'name' => 'Transporte',           'description' => 'Vías de acceso, estado de semáforos, paraderos y movilidad urbana.',              'parent' => null,  'active' => true,  'comment_count' => 6,  'trend' => 'down', 'sub_count' => 1],
        ['slug' => 'medio_ambiente',  'name' => 'Medio ambiente',       'description' => 'Áreas verdes, reciclaje, gestión del agua y contaminación ambiental.',             'parent' => null,  'active' => false, 'comment_count' => 11, 'trend' => 'up',   'sub_count' => 3],
    ],
]);

$kpis = computed(function () {
    $total   = count($this->topics);
    $active  = count(array_filter($this->topics, fn($t) => $t['active']));
    $subs    = array_sum(array_column($this->topics, 'sub_count'));
    return ['total' => $total, 'active' => $active, 'subs' => $subs];
});

$toggleActive = function (string $slug) {
    $this->topics = array_map(function ($t) use ($slug) {
        if ($t['slug'] === $slug) {
            $t['active'] = !$t['active'];
        }
        return $t;
    }, $this->topics);
};

?>

<div
    x-data="{
        slideOpen: false,
        form: { name: '', slug: '', description: '', parent: '' },
        generateSlug() {
            this.form.slug = this.form.name
                .toLowerCase()
                .normalize('NFD').replace(/[̀-ͯ]/g, '')
                .replace(/[^a-z0-9\s_]/g, '')
                .trim()
                .replace(/\s+/g, '_');
        }
    }"
>
    {{-- Page header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="font-serif text-2xl font-bold text-ink-deep">Vocabulario de temas</h1>
            <p class="mt-1 text-sm text-ink-soft">Taxonomía semántica para clasificación de comentarios ciudadanos</p>
        </div>
        <button
            @click="slideOpen = true"
            class="btn-primary flex items-center gap-2"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
            </svg>
            Nuevo tema
        </button>
    </div>

    {{-- KPI cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="card flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-brand-canopy/10 flex items-center justify-center flex-shrink-0">
                <x-icon name="brain" class="w-6 h-6 text-brand-canopy" />
            </div>
            <div>
                <p class="text-xs text-ink-soft font-medium uppercase tracking-wide">Total temas</p>
                <p class="text-3xl font-bold text-ink-deep font-serif">{{ $this->kpis['total'] }}</p>
            </div>
        </div>
        <div class="card flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-brand-river/10 flex items-center justify-center flex-shrink-0">
                <x-icon name="bar-chart" class="w-6 h-6 text-brand-river" />
            </div>
            <div>
                <p class="text-xs text-ink-soft font-medium uppercase tracking-wide">Temas activos</p>
                <p class="text-3xl font-bold text-brand-river font-serif">{{ $this->kpis['active'] }}</p>
            </div>
        </div>
        <div class="card flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-brand-gold/10 flex items-center justify-center flex-shrink-0">
                <x-icon name="file-text" class="w-6 h-6 text-brand-gold" />
            </div>
            <div>
                <p class="text-xs text-ink-soft font-medium uppercase tracking-wide">Sub-temas</p>
                <p class="text-3xl font-bold text-brand-gold font-serif">{{ $this->kpis['subs'] }}</p>
            </div>
        </div>
    </div>

    {{-- Topics grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach ($topics as $topic)
            @php
                $trendConfig = [
                    'up'   => ['icon' => '↑', 'class' => 'bg-brand-river/10 text-brand-river',  'label' => 'Creciendo'],
                    'down' => ['icon' => '↓', 'class' => 'bg-brand-clay/10 text-brand-clay',    'label' => 'Bajando'],
                    'flat' => ['icon' => '→', 'class' => 'bg-ink-soft/10 text-ink-soft',        'label' => 'Estable'],
                ];
                $trend = $trendConfig[$topic['trend']] ?? $trendConfig['flat'];
            @endphp
            <div class="card group flex flex-col gap-4 hover:shadow-brand-lg transition-shadow duration-200 {{ !$topic['active'] ? 'opacity-60' : '' }}">
                {{-- Card header --}}
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <h3 class="font-serif text-lg font-bold text-ink-deep leading-snug">{{ $topic['name'] }}</h3>
                        <code class="text-xs font-mono text-brand-canopy bg-brand-canopy/8 px-1.5 py-0.5 rounded mt-1 inline-block">{{ $topic['slug'] }}</code>
                    </div>
                    <span class="ml-3 inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold {{ $trend['class'] }} flex-shrink-0">
                        {{ $trend['icon'] }} {{ $trend['label'] }}
                    </span>
                </div>

                {{-- Description --}}
                <p class="text-sm text-ink-soft leading-relaxed flex-1">{{ $topic['description'] }}</p>

                {{-- Meta --}}
                <div class="flex items-center justify-between pt-2 border-t border-brand-mist">
                    <div class="flex items-center gap-4 text-xs text-ink-soft">
                        <span class="flex items-center gap-1">
                            <x-icon name="message-square" class="w-3.5 h-3.5" />
                            <strong class="text-ink-deep">{{ $topic['comment_count'] }}</strong> comentarios
                        </span>
                        <span class="flex items-center gap-1">
                            <x-icon name="database" class="w-3.5 h-3.5" />
                            <strong class="text-ink-deep">{{ $topic['sub_count'] }}</strong> sub-temas
                        </span>
                    </div>
                    @if ($topic['active'])
                        <span class="w-2 h-2 rounded-full bg-brand-river inline-block" title="Activo"></span>
                    @else
                        <span class="w-2 h-2 rounded-full bg-brand-clay inline-block" title="Inactivo"></span>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2">
                    <button
                        @click="slideOpen = true; form.name = '{{ addslashes($topic['name']) }}'; form.slug = '{{ $topic['slug'] }}'; form.description = '{{ addslashes($topic['description']) }}';"
                        class="flex-1 py-1.5 px-3 text-xs font-medium text-brand-canopy border border-brand-canopy/30 rounded-lg hover:bg-brand-canopy/10 transition-colors"
                    >
                        Editar
                    </button>
                    <button
                        wire:click="toggleActive('{{ $topic['slug'] }}')"
                        class="flex-1 py-1.5 px-3 text-xs font-medium rounded-lg border transition-colors {{ $topic['active'] ? 'text-brand-clay border-brand-clay/30 hover:bg-brand-clay/10' : 'text-brand-river border-brand-river/30 hover:bg-brand-river/10' }}"
                    >
                        {{ $topic['active'] ? 'Desactivar' : 'Activar' }}
                    </button>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Slide-over panel --}}
    <div
        x-show="slideOpen"
        x-cloak
        class="fixed inset-0 z-40 flex"
    >
        {{-- Backdrop --}}
        <div
            class="absolute inset-0 bg-ink-deep/40 backdrop-blur-sm"
            @click="slideOpen = false"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        ></div>

        {{-- Panel --}}
        <div
            class="absolute right-0 top-0 h-full w-full max-w-md bg-white shadow-brand-lg flex flex-col"
            x-transition:enter="transition ease-out duration-250"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
        >
            {{-- Panel header --}}
            <div class="flex items-center justify-between px-6 py-4 border-b border-brand-mist bg-brand-mist/40">
                <div>
                    <h2 class="font-serif text-lg font-bold text-ink-deep">Tema</h2>
                    <p class="text-xs text-ink-soft mt-0.5">Configura el vocabulario semántico</p>
                </div>
                <button
                    @click="slideOpen = false"
                    class="p-2 rounded-lg text-ink-soft hover:bg-brand-mist hover:text-ink-deep transition-colors"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Panel body --}}
            <div class="flex-1 overflow-y-auto px-6 py-6 space-y-5">
                {{-- Nombre --}}
                <div>
                    <label class="block text-xs font-semibold text-ink-deep uppercase tracking-wide mb-1.5">
                        Nombre <span class="text-brand-clay">*</span>
                    </label>
                    <input
                        type="text"
                        x-model="form.name"
                        @input="generateSlug()"
                        placeholder="Ej: Alumbrado público"
                        class="w-full px-3 py-2.5 text-sm border border-brand-mist rounded-lg bg-white text-ink-deep placeholder-ink-soft/60 focus:outline-none focus:ring-2 focus:ring-brand-canopy/40 focus:border-brand-canopy"
                    />
                </div>

                {{-- Slug --}}
                <div>
                    <label class="block text-xs font-semibold text-ink-deep uppercase tracking-wide mb-1.5">
                        Slug (auto-generado)
                    </label>
                    <input
                        type="text"
                        x-model="form.slug"
                        placeholder="alumbrado_publico"
                        class="w-full px-3 py-2.5 text-sm border border-brand-mist rounded-lg bg-brand-mist/40 text-brand-canopy font-mono placeholder-ink-soft/40 focus:outline-none focus:ring-2 focus:ring-brand-canopy/40 focus:border-brand-canopy"
                    />
                    <p class="text-xs text-ink-soft mt-1">Identificador único para uso interno y API.</p>
                </div>

                {{-- Descripción --}}
                <div>
                    <label class="block text-xs font-semibold text-ink-deep uppercase tracking-wide mb-1.5">
                        Descripción
                    </label>
                    <textarea
                        x-model="form.description"
                        rows="4"
                        placeholder="Describe el alcance y los tipos de comentarios que agrupa este tema…"
                        class="w-full px-3 py-2.5 text-sm border border-brand-mist rounded-lg bg-white text-ink-deep placeholder-ink-soft/60 focus:outline-none focus:ring-2 focus:ring-brand-canopy/40 focus:border-brand-canopy resize-none"
                    ></textarea>
                </div>

                {{-- Tema padre --}}
                <div>
                    <label class="block text-xs font-semibold text-ink-deep uppercase tracking-wide mb-1.5">
                        Tema padre
                    </label>
                    <select
                        x-model="form.parent"
                        class="w-full px-3 py-2.5 text-sm border border-brand-mist rounded-lg bg-white text-ink-deep focus:outline-none focus:ring-2 focus:ring-brand-canopy/40 focus:border-brand-canopy"
                    >
                        <option value="">— Ninguno (tema raíz) —</option>
                        @foreach ($topics as $t)
                            <option value="{{ $t['slug'] }}">{{ $t['name'] }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-ink-soft mt-1">Selecciona si este es un sub-tema de otro.</p>
                </div>

                {{-- Info note --}}
                <div class="rounded-lg bg-brand-river/8 border border-brand-river/20 px-4 py-3">
                    <p class="text-xs text-brand-river leading-relaxed">
                        Los cambios son de UI únicamente en esta fase. La persistencia en base de datos se habilitará en F3.
                    </p>
                </div>
            </div>

            {{-- Panel footer --}}
            <div class="flex items-center gap-3 px-6 py-4 border-t border-brand-mist bg-brand-mist/30">
                <button
                    @click="slideOpen = false; form = { name: '', slug: '', description: '', parent: '' };"
                    class="flex-1 py-2.5 px-4 text-sm font-medium text-ink-soft border border-brand-mist rounded-lg bg-white hover:bg-brand-mist hover:text-ink-deep transition-colors"
                >
                    Cancelar
                </button>
                <button
                    @click="slideOpen = false; form = { name: '', slug: '', description: '', parent: '' };"
                    class="flex-1 btn-primary py-2.5"
                >
                    Guardar tema
                </button>
            </div>
        </div>
    </div>
</div>
