<?php

use function Livewire\Volt\title;
use function Livewire\Volt\state;
use function Livewire\Volt\mount;

title('Chat RAG · SIIM');

state([
    'messages' => [],
    'input'    => '',
    'waiting'  => false,
    'filters'  => [
        'rango'       => '7d',
        'canal'       => 'todos',
        'tema'        => 'todos',
        'sentimiento' => 'todos',
    ],
]);

mount(function () {
    $this->messages = [
        [
            'role'    => 'assistant',
            'content' => 'Soy el asistente RAG. Pregúntame sobre la percepción ciudadana — por ejemplo: "¿Qué dicen sobre obras esta semana?" o "¿Cuántos comentarios negativos hay?"',
            'time'    => now()->format('H:i'),
        ],
    ];
});

$send = function () {
    $text = trim($this->input);
    if ($text === '' || $this->waiting) {
        return;
    }

    $this->messages[] = [
        'role' => 'user',
        'content' => $text,
        'time' => now()->format('H:i'),
    ];
    $this->input = '';
    $this->waiting = true;

    // Normalizar: lowercase + remover tildes para matchear con typos comunes
    $normalized = mb_strtolower(strtr($text, [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ñ' => 'n',
    ]));

    $any = fn (array $needles): bool => collect($needles)->contains(
        fn (string $n): bool => str_contains($normalized, $n)
    );

    if ($any(['hola', 'buenos dias', 'buenas', 'que tal', 'saludos', 'hey'])) {
        $reply = '¡Hola! Soy el asistente RAG del SIIM. Puedo analizar lo que dice la ciudadanía sobre cualquier tema — obras, seguridad, salud, limpieza, etc. ¿Sobre qué quiere saber hoy?';
    } elseif ($any(['obras', 'orbas', 'obra', 'pavimento', 'pavimentacion', 'pista', 'plaza', 'parque', 'infraestructura'])) {
        $reply = "Sobre **Obras públicas** (últimos 7 días):\n• 38 menciones totales\n• 60% positivas → parque infantil del barrio La Esperanza, pavimentación de la av. La Marginal\n• 25% negativas → estado del pavimento en sectores periféricos, demora en cierre de zanjas\n• 15% neutrales → consultas sobre cronograma\nTrending: parques + alumbrado.";
    } elseif ($any(['seguridad', 'segurida', 'serenazgo', 'delincuencia', 'robo', 'patrullaje', 'operativo'])) {
        $reply = "Sobre **Seguridad ciudadana**:\n• 27 menciones, sube 12% vs semana pasada\n• 70% positivas → operativos del serenazgo, presencia visible en plazas\n• 30% negativas → tramos sin alumbrado en jirón Tarma y sector La Florida\nSugerencia: priorizar respuesta en los 2 sectores citados.";
    } elseif ($any(['salud', 'postas', 'vacuna', 'medico', 'centro de salud'])) {
        $reply = "Sobre **Salud**:\n• 18 menciones, 85% positivas\n• Tópicos top: campaña de vacunación, posta médica El Milagro, atención en consulta externa\n• Negativos: 2 menciones sobre horarios limitados\nSentimiento general muy favorable.";
    } elseif ($any(['limpieza', 'basura', 'residuos', 'recoleccion', 'recojo'])) {
        $reply = "Sobre **Limpieza pública**:\n• 22 menciones, sentimiento mixto\n• Quejas principales: recolección irregular en calle Junín y jirón Cusco (7 menciones)\n• Elogios: limpieza mercado central post-feria\n• Trending: ↓ desde el miércoles tras nuevo cronograma.";
    } elseif ($any(['tributo', 'impuesto', 'predial', 'arbitrio', 'pago'])) {
        $reply = "Sobre **Tributos municipales**:\n• 14 menciones, mayormente neutrales (consultas)\n• Top consultas: dónde pagar predial, horarios banco municipal, descuentos vigentes\n• 3 quejas sobre cola en ventanilla.";
    } elseif ($any(['educacion', 'taller', 'beca', 'biblioteca'])) {
        $reply = "Sobre **Educación**:\n• 11 menciones, 80% positivas\n• Más mencionado: talleres municipales de verano, biblioteca municipal\n• 2 sugerencias para extender horario sabatino.";
    } elseif ($any(['transporte', 'transito', 'semaforo', 'paradero', 'colectivo'])) {
        $reply = "Sobre **Transporte**:\n• 9 menciones, sentimiento negativo (60%)\n• Reclamo top: falta de semáforo intersección Sucre/Tarapacá\n• 3 quejas sobre paraderos sin techo.";
    } elseif ($any(['ambiente', 'reciclaje', 'arboles', 'agua', 'rio'])) {
        $reply = "Sobre **Medio ambiente**:\n• 7 menciones, 70% positivas\n• Elogios al programa de reciclaje y siembra de árboles\n• 2 alertas sobre contaminación visible en quebrada local.";
    } elseif ($any(['negativ', 'problemas', 'reclam', 'queja', 'critica', 'mal', 'mlas', 'malas'])) {
        $reply = "**Top 5 reclamos esta semana:**\n1) Alumbrado público apagado en jirón Tarma (12)\n2) Pavimento av. La Marginal en mal estado (9)\n3) Recolección residuos irregular calle Junín (7)\n4) Falta de semáforo intersección Sucre/Tarapacá (5)\n5) Cola en ventanilla tributos (3)";
    } elseif ($any(['positiv', 'felicit', 'elogio', 'gracias', 'buena gestion'])) {
        $reply = "**Top 5 elogios esta semana:**\n1) Nueva plaza del barrio La Esperanza (15)\n2) Operativos de seguridad ciudadana (11)\n3) Campañas de salud preventiva (8)\n4) Talleres municipales de verano (6)\n5) Limpieza del mercado central (4)";
    } elseif ($any(['cuanto', 'cuantos', 'total', 'numero', 'cantidad', 'estadistica'])) {
        $reply = "**Estadísticas del rango aplicado:**\n• Comentarios totales: 247\n• Positivos: 158 (64%)\n• Neutrales: 56 (23%)\n• Negativos: 33 (13%)\n• Canal top: Facebook (118)\n• Tema más recurrente: Obras públicas (38)";
    } elseif ($any(['facebook', 'fb'])) {
        $reply = "**Comentarios desde Facebook** (últimos 7 días):\n• 118 menciones (47% del total)\n• Sentimiento: 70% positivo, 18% neutral, 12% negativo\n• Posts con más engagement: anuncios de obras + agenda cultural municipal.";
    } elseif ($any(['instagram', 'ig'])) {
        $reply = "**Instagram** muestra una audiencia más joven:\n• 76 menciones\n• 85% positivas, sobre todo en publicaciones de eventos culturales y deportivos\n• Negativos limitados a 5 menciones sobre demora en respuestas a DMs.";
    } elseif (mb_strlen($normalized) < 5) {
        $reply = 'Necesito un poco más de contexto para ayudarle. Pregunte por algún tema (obras, seguridad, salud, limpieza, tributos, educación, transporte, medio ambiente) o pida estadísticas generales.';
    } else {
        $reply = "No identifiqué un tema específico en su consulta sobre \"" . mb_strimwidth($text, 0, 60, '…') . "\".\n\nPruebe con palabras clave como: obras, seguridad, salud, limpieza, reclamos, elogios, estadísticas, o el nombre de un canal (Facebook, Instagram). También puede usar los atajos rápidos del panel izquierdo.";
    }

    $this->messages[] = [
        'role' => 'assistant',
        'content' => $reply,
        'time' => now()->format('H:i'),
    ];
    $this->waiting = false;
};

$injectPrompt = function (string $prompt) {
    $this->input = $prompt;
};

$applyFilters = function () {
    // mock — filters applied, UI already reflects state
};

?>

<div class="flex flex-col gap-4" x-data>

    {{-- Header --}}
    <div class="card flex items-center gap-4">
        <div class="w-12 h-12 bg-brand-canopy/10 rounded-xl flex items-center justify-center text-brand-canopy flex-shrink-0">
            <x-icon name="brain" class="w-7 h-7" />
        </div>
        <div>
            <h1 class="font-serif text-2xl font-bold text-brand-canopy">Chat RAG · Análisis ciudadano</h1>
            <p class="text-sm text-ink-soft mt-0.5">Pregunta en lenguaje natural sobre comentarios analizados</p>
        </div>
    </div>

    {{-- Body: sidebar + chat --}}
    <div class="flex gap-4 items-start">

        {{-- Sidebar izquierda --}}
        <div class="w-1/4 flex flex-col gap-4">

            {{-- Filtros --}}
            <div class="card">
                <h2 class="font-serif text-sm font-semibold text-brand-canopy mb-3 flex items-center gap-1.5">
                    <x-icon name="search" class="w-4 h-4" /> Filtros de contexto
                </h2>

                {{-- Rango --}}
                <div class="mb-4">
                    <p class="text-xs font-medium text-ink-soft mb-2">Rango de fechas</p>
                    <div class="grid grid-cols-2 gap-1">
                        @foreach(['hoy' => 'Hoy', '7d' => '7 días', '30d' => '30 días', '90d' => '90 días'] as $val => $label)
                            <button
                                wire:click="$set('filters.rango', '{{ $val }}')"
                                class="text-xs px-2 py-1.5 rounded-lg border transition-colors {{ $filters['rango'] === $val ? 'bg-brand-canopy text-white border-brand-canopy' : 'border-brand-canopy/20 text-ink-soft hover:border-brand-canopy/50' }}"
                            >{{ $label }}</button>
                        @endforeach
                    </div>
                </div>

                {{-- Canal --}}
                <div class="mb-4">
                    <p class="text-xs font-medium text-ink-soft mb-2">Canal</p>
                    <div class="flex flex-wrap gap-1">
                        @foreach(['todos' => 'Todos', 'facebook' => 'Facebook', 'twitter' => 'Twitter', 'formulario' => 'Formulario', 'instagram' => 'Instagram', 'email' => 'Email'] as $val => $label)
                            <button
                                wire:click="$set('filters.canal', '{{ $val }}')"
                                class="text-xs px-2 py-1 rounded-full border transition-colors {{ $filters['canal'] === $val ? 'bg-brand-river text-white border-brand-river' : 'border-brand-river/20 text-ink-soft hover:border-brand-river/50' }}"
                            >{{ $label }}</button>
                        @endforeach
                    </div>
                </div>

                {{-- Tema --}}
                <div class="mb-4">
                    <p class="text-xs font-medium text-ink-soft mb-2">Tema</p>
                    <div class="flex flex-wrap gap-1">
                        @foreach(['todos' => 'Todos', 'obras' => 'Obras', 'seguridad' => 'Seguridad', 'salud' => 'Salud', 'transporte' => 'Transporte', 'educacion' => 'Educación', 'ambiente' => 'Ambiente', 'turismo' => 'Turismo', 'otros' => 'Otros'] as $val => $label)
                            <button
                                wire:click="$set('filters.tema', '{{ $val }}')"
                                class="text-xs px-2 py-1 rounded-full border transition-colors {{ $filters['tema'] === $val ? 'bg-brand-gold text-white border-brand-gold' : 'border-brand-gold/30 text-ink-soft hover:border-brand-gold/60' }}"
                            >{{ $label }}</button>
                        @endforeach
                    </div>
                </div>

                {{-- Sentimiento --}}
                <div class="mb-4">
                    <p class="text-xs font-medium text-ink-soft mb-2">Sentimiento</p>
                    <div class="flex gap-1">
                        @foreach(['todos' => 'Todos', 'positivo' => 'Positivo', 'neutral' => 'Neutral', 'negativo' => 'Negativo'] as $val => $label)
                            <button
                                wire:click="$set('filters.sentimiento', '{{ $val }}')"
                                class="flex-1 text-xs px-1.5 py-1.5 rounded-lg border transition-colors
                                    {{ $filters['sentimiento'] === $val && $val === 'positivo' ? 'bg-sentiment-positive text-white border-transparent' : '' }}
                                    {{ $filters['sentimiento'] === $val && $val === 'neutral' ? 'bg-sentiment-neutral text-white border-transparent' : '' }}
                                    {{ $filters['sentimiento'] === $val && $val === 'negativo' ? 'bg-sentiment-negative text-white border-transparent' : '' }}
                                    {{ $filters['sentimiento'] === $val && $val === 'todos' ? 'bg-brand-canopy text-white border-brand-canopy' : '' }}
                                    {{ $filters['sentimiento'] !== $val ? 'border-gray-200 text-ink-soft hover:border-gray-300' : '' }}"
                            >{{ $label }}</button>
                        @endforeach
                    </div>
                </div>

                <button wire:click="applyFilters" class="w-full btn-primary text-sm py-2">
                    Aplicar filtros
                </button>
            </div>

            {{-- Atajos rápidos --}}
            <div class="card">
                <h2 class="font-serif text-sm font-semibold text-brand-canopy mb-3 flex items-center gap-1.5">
                    <x-icon name="message-square" class="w-4 h-4" /> Atajos rápidos
                </h2>
                <div class="flex flex-col gap-2">
                    @foreach([
                        '¿Qué dicen sobre obras?',
                        'Top reclamos esta semana',
                        'Sentimiento últimos 30 días',
                        'Comentarios de Facebook',
                    ] as $atajo)
                        <button
                            wire:click="injectPrompt('{{ $atajo }}')"
                            class="text-left text-xs px-3 py-2 rounded-lg border border-brand-canopy/20 text-ink-soft hover:bg-brand-canopy/5 hover:border-brand-canopy/40 hover:text-brand-canopy transition-colors"
                        >{{ $atajo }}</button>
                    @endforeach
                </div>
            </div>

        </div>

        {{-- Chat area --}}
        <div class="flex-1 card flex flex-col overflow-hidden" style="height: calc(100vh - 220px);">

            {{-- Banner simulación --}}
            <div class="bg-brand-river/10 border border-brand-river/20 rounded-lg px-4 py-2.5 mb-3 flex items-center gap-2 text-xs text-brand-river flex-shrink-0">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Esta versión usa respuestas simuladas. La conexión LLM real se activa en F6 — Chat RAG.
            </div>

            {{-- Mensajes --}}
            <div
                class="flex-1 overflow-y-auto flex flex-col gap-3 pr-1 mb-3"
                id="chat-messages"
                x-init="$watch('$wire.messages', () => { $nextTick(() => { let el = $el; el.scrollTop = el.scrollHeight; }); })"
            >
                @foreach ($messages as $msg)
                    @if ($msg['role'] === 'user')
                        <div class="flex justify-end gap-2">
                            <div class="max-w-[75%]">
                                <div class="bg-brand-canopy text-white rounded-2xl rounded-tr-sm px-4 py-3 text-sm leading-relaxed">
                                    {{ $msg['content'] }}
                                </div>
                                <p class="text-right text-[10px] text-ink-soft mt-1 pr-1">{{ $msg['time'] }}</p>
                            </div>
                            <div class="w-8 h-8 bg-brand-canopy/10 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                <x-icon name="users" class="w-4 h-4 text-brand-canopy" />
                            </div>
                        </div>
                    @else
                        <div class="flex gap-2">
                            <div class="w-8 h-8 bg-brand-gold/15 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                                <x-icon name="brain" class="w-4 h-4 text-brand-gold" />
                            </div>
                            <div class="max-w-[75%]">
                                <div class="bg-brand-mist border border-brand-canopy/10 rounded-2xl rounded-tl-sm px-4 py-3 text-sm leading-relaxed text-ink-deep">
                                    {{ $msg['content'] }}
                                </div>
                                <p class="text-[10px] text-ink-soft mt-1 pl-1">{{ $msg['time'] }}</p>
                            </div>
                        </div>
                    @endif
                @endforeach

                @if ($waiting)
                    <div class="flex gap-2">
                        <div class="w-8 h-8 bg-brand-gold/15 rounded-full flex items-center justify-center flex-shrink-0 mt-1">
                            <x-icon name="brain" class="w-4 h-4 text-brand-gold" />
                        </div>
                        <div class="bg-brand-mist border border-brand-canopy/10 rounded-2xl rounded-tl-sm px-4 py-3">
                            <span class="flex gap-1">
                                <span class="w-2 h-2 bg-brand-canopy/40 rounded-full animate-bounce" style="animation-delay:0ms"></span>
                                <span class="w-2 h-2 bg-brand-canopy/40 rounded-full animate-bounce" style="animation-delay:150ms"></span>
                                <span class="w-2 h-2 bg-brand-canopy/40 rounded-full animate-bounce" style="animation-delay:300ms"></span>
                            </span>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Input --}}
            <div class="flex-shrink-0 border-t border-gray-100 pt-3">
                <div class="flex gap-2 items-end">
                    <textarea
                        wire:model="input"
                        rows="1"
                        placeholder="Escribe tu pregunta sobre los comentarios ciudadanos..."
                        class="flex-1 resize-none border border-gray-200 rounded-xl px-4 py-3 text-sm text-ink-deep placeholder-ink-soft focus:outline-none focus:ring-2 focus:ring-brand-canopy/30 focus:border-brand-canopy/50 transition-all"
                        style="min-height:48px; max-height:120px;"
                        x-on:keydown.enter.prevent="if(!$event.shiftKey) { $wire.send(); }"
                        x-on:input="$el.style.height='48px'; $el.style.height=Math.min($el.scrollHeight,120)+'px';"
                    ></textarea>
                    <button
                        wire:click="send"
                        wire:loading.attr="disabled"
                        class="btn-primary flex-shrink-0 px-4 py-3 rounded-xl disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        <span class="text-sm font-medium hidden sm:inline">Enviar</span>
                    </button>
                </div>
                <p class="text-[10px] text-ink-soft mt-1.5 pl-1">Enter para enviar · Shift+Enter para nueva línea</p>
            </div>

        </div>

    </div>

</div>
