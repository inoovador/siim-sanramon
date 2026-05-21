<?php

use function Livewire\Volt\title;
use function Livewire\Volt\state;
use function Livewire\Volt\computed;

title('Comentarios · SIIM');

state([
    'search'       => '',
    'filterPolarity' => '',
    'filterChannel'  => '',
    'filterTopic'    => '',
    'filterDays'     => '',
    'perPage'        => 10,
    'page'           => 1,

    'allComments' => [
        ['id'=>1,  'author_alias'=>'Juan P.',    'text'=>'El parque infantil quedó hermoso, gracias al alcalde por la inversión.',         'channel'=>'meta_facebook',   'polarity'=>'positive','score'=>0.92,'confidence'=>0.95,'topics'=>['obras_publicas'],'captured_at'=>'2026-05-20 08:14'],
        ['id'=>2,  'author_alias'=>'María C.',   'text'=>'Excelente operativo de seguridad este fin de semana, me siento más tranquila.',  'channel'=>'meta_instagram',  'polarity'=>'positive','score'=>0.88,'confidence'=>0.91,'topics'=>['seguridad'],'captured_at'=>'2026-05-20 09:32'],
        ['id'=>3,  'author_alias'=>'Carlos M.',  'text'=>'La campaña de salud preventiva en el colegio Mariscal fue muy útil.',            'channel'=>'web_form',        'polarity'=>'positive','score'=>0.85,'confidence'=>0.89,'topics'=>['salud','educacion'],'captured_at'=>'2026-05-19 14:05'],
        ['id'=>4,  'author_alias'=>'Lucía R.',   'text'=>'¿Cuándo retoman la recolección de residuos en mi calle Progreso?',              'channel'=>'public_chatbot',  'polarity'=>'neutral', 'score'=>0.05,'confidence'=>0.82,'topics'=>['limpieza'],'captured_at'=>'2026-05-19 11:20'],
        ['id'=>5,  'author_alias'=>'Pedro S.',   'text'=>'Solicito información sobre el horario del banco municipal para pagos.',          'channel'=>'web_form',        'polarity'=>'neutral', 'score'=>0.02,'confidence'=>0.78,'topics'=>['tributos'],'captured_at'=>'2026-05-19 10:47'],
        ['id'=>6,  'author_alias'=>'Anónimo',    'text'=>'¿Dónde puedo pagar mi impuesto predial este mes?',                              'channel'=>'public_chatbot',  'polarity'=>'neutral', 'score'=>0.01,'confidence'=>0.80,'topics'=>['tributos'],'captured_at'=>'2026-05-18 16:33'],
        ['id'=>7,  'author_alias'=>'Andrea V.',  'text'=>'El pavimento de la av. Marginal está pésimo, hay huecos enormes.',              'channel'=>'meta_facebook',   'polarity'=>'negative','score'=>-0.87,'confidence'=>0.93,'topics'=>['obras_publicas','transporte'],'captured_at'=>'2026-05-18 07:55'],
        ['id'=>8,  'author_alias'=>'Marco T.',   'text'=>'No funciona el alumbrado en el jirón Tarma hace tres semanas.',                 'channel'=>'csv_upload',      'polarity'=>'negative','score'=>-0.82,'confidence'=>0.90,'topics'=>['obras_publicas'],'captured_at'=>'2026-05-17 20:10'],
        ['id'=>9,  'author_alias'=>'Diana L.',   'text'=>'Hay un poste a punto de caer en Circunvalación, es peligroso.',                 'channel'=>'meta_instagram',  'polarity'=>'negative','score'=>-0.91,'confidence'=>0.96,'topics'=>['obras_publicas','seguridad'],'captured_at'=>'2026-05-17 18:42'],
        ['id'=>10, 'author_alias'=>'Sofía A.',   'text'=>'Gracias por instalar los tachos de reciclaje en el parque central.',            'channel'=>'web_form',        'polarity'=>'positive','score'=>0.80,'confidence'=>0.87,'topics'=>['medio_ambiente','limpieza'],'captured_at'=>'2026-05-17 12:00'],
        ['id'=>11, 'author_alias'=>'Miguel B.',  'text'=>'El serenazgo respondió rápido cuando llamé, muy bien.',                         'channel'=>'meta_facebook',   'polarity'=>'positive','score'=>0.83,'confidence'=>0.88,'topics'=>['seguridad'],'captured_at'=>'2026-05-16 21:30'],
        ['id'=>12, 'author_alias'=>'Rosa N.',    'text'=>'¿Hay talleres municipales para jóvenes este mes?',                              'channel'=>'public_chatbot',  'polarity'=>'neutral', 'score'=>0.03,'confidence'=>0.75,'topics'=>['educacion'],'captured_at'=>'2026-05-16 15:18'],
        ['id'=>13, 'author_alias'=>'José F.',    'text'=>'Los semáforos del cruce con la panamericana no funcionan bien.',                 'channel'=>'meta_instagram',  'polarity'=>'negative','score'=>-0.75,'confidence'=>0.85,'topics'=>['transporte'],'captured_at'=>'2026-05-16 08:05'],
        ['id'=>14, 'author_alias'=>'Carmen Q.',  'text'=>'La posta médica del sector 3 atiende muy bien, felicitaciones al personal.',    'channel'=>'web_form',        'polarity'=>'positive','score'=>0.90,'confidence'=>0.94,'topics'=>['salud'],'captured_at'=>'2026-05-15 14:22'],
        ['id'=>15, 'author_alias'=>'Luis D.',    'text'=>'Falta iluminación en el pasaje Los Pinos, es peligroso de noche.',              'channel'=>'csv_upload',      'polarity'=>'negative','score'=>-0.80,'confidence'=>0.89,'topics'=>['seguridad','obras_publicas'],'captured_at'=>'2026-05-15 19:48'],
        ['id'=>16, 'author_alias'=>'Juan P.',    'text'=>'Buena campaña de vacunación, mi familia ya se vacunó.',                         'channel'=>'meta_facebook',   'polarity'=>'positive','score'=>0.86,'confidence'=>0.92,'topics'=>['salud'],'captured_at'=>'2026-05-15 10:33'],
        ['id'=>17, 'author_alias'=>'Andrea V.',  'text'=>'El río Tarma está muy contaminado, necesitan intervenir.',                      'channel'=>'meta_instagram',  'polarity'=>'negative','score'=>-0.85,'confidence'=>0.91,'topics'=>['medio_ambiente'],'captured_at'=>'2026-05-14 16:10'],
        ['id'=>18, 'author_alias'=>'Pedro S.',   'text'=>'¿Cuándo inician las obras de la nueva plaza de armas?',                         'channel'=>'public_chatbot',  'polarity'=>'neutral', 'score'=>0.04,'confidence'=>0.77,'topics'=>['obras_publicas'],'captured_at'=>'2026-05-14 11:00'],
        ['id'=>19, 'author_alias'=>'Anónimo',    'text'=>'Me cobraron de más en arbitrios, quiero reclamar.',                             'channel'=>'web_form',        'polarity'=>'negative','score'=>-0.70,'confidence'=>0.83,'topics'=>['tributos'],'captured_at'=>'2026-05-14 09:15'],
        ['id'=>20, 'author_alias'=>'Sofía A.',   'text'=>'El taller de computación municipal es excelente para los adultos mayores.',     'channel'=>'meta_facebook',   'polarity'=>'positive','score'=>0.88,'confidence'=>0.90,'topics'=>['educacion'],'captured_at'=>'2026-05-13 16:45'],
        ['id'=>21, 'author_alias'=>'Marco T.',   'text'=>'Las bermas del jr. San Martín están llenas de basura.',                         'channel'=>'csv_upload',      'polarity'=>'negative','score'=>-0.78,'confidence'=>0.88,'topics'=>['limpieza'],'captured_at'=>'2026-05-13 08:30'],
        ['id'=>22, 'author_alias'=>'Diana L.',   'text'=>'¿Puedo solicitar licencia de funcionamiento por la web?',                       'channel'=>'public_chatbot',  'polarity'=>'neutral', 'score'=>0.02,'confidence'=>0.74,'topics'=>['tributos'],'captured_at'=>'2026-05-13 12:50'],
        ['id'=>23, 'author_alias'=>'Rosa N.',    'text'=>'Muy buena la obra de encauzamiento del canal, ya no inunda.',                   'channel'=>'meta_instagram',  'polarity'=>'positive','score'=>0.87,'confidence'=>0.93,'topics'=>['obras_publicas','medio_ambiente'],'captured_at'=>'2026-05-12 17:20'],
        ['id'=>24, 'author_alias'=>'Carlos M.',  'text'=>'Los paraderos del centro están en mal estado.',                                  'channel'=>'meta_facebook',   'polarity'=>'negative','score'=>-0.72,'confidence'=>0.84,'topics'=>['transporte'],'captured_at'=>'2026-05-12 09:05'],
        ['id'=>25, 'author_alias'=>'Luis D.',    'text'=>'¿Cuándo habrá campaña de esterilización de mascotas?',                          'channel'=>'public_chatbot',  'polarity'=>'neutral', 'score'=>0.01,'confidence'=>0.71,'topics'=>['salud','medio_ambiente'],'captured_at'=>'2026-05-12 14:30'],
        ['id'=>26, 'author_alias'=>'Miguel B.',  'text'=>'Muy buenas las áreas verdes del parque Ecológico, bien mantenidas.',            'channel'=>'web_form',        'polarity'=>'positive','score'=>0.84,'confidence'=>0.89,'topics'=>['medio_ambiente'],'captured_at'=>'2026-05-11 10:10'],
        ['id'=>27, 'author_alias'=>'Carmen Q.',  'text'=>'No hay señalización adecuada en la carretera central.',                         'channel'=>'csv_upload',      'polarity'=>'negative','score'=>-0.76,'confidence'=>0.86,'topics'=>['transporte','seguridad'],'captured_at'=>'2026-05-11 07:40'],
        ['id'=>28, 'author_alias'=>'José F.',    'text'=>'La biblioteca municipal tiene buenos recursos, felicito a los responsables.',   'channel'=>'meta_facebook',   'polarity'=>'positive','score'=>0.81,'confidence'=>0.87,'topics'=>['educacion'],'captured_at'=>'2026-05-10 15:55'],
        ['id'=>29, 'author_alias'=>'Anónimo',    'text'=>'¿A qué hora abre el área de rentas de la municipalidad?',                      'channel'=>'public_chatbot',  'polarity'=>'neutral', 'score'=>0.00,'confidence'=>0.70,'topics'=>['tributos'],'captured_at'=>'2026-05-10 09:22'],
        ['id'=>30, 'author_alias'=>'Juan P.',    'text'=>'Los tachos de basura del mercado están desbordando.',                           'channel'=>'meta_instagram',  'polarity'=>'negative','score'=>-0.83,'confidence'=>0.90,'topics'=>['limpieza'],'captured_at'=>'2026-05-10 11:00'],
        ['id'=>31, 'author_alias'=>'Andrea V.',  'text'=>'El operativo de serenazgo en la feria del domingo fue muy ordenado.',           'channel'=>'meta_facebook',   'polarity'=>'positive','score'=>0.86,'confidence'=>0.91,'topics'=>['seguridad'],'captured_at'=>'2026-05-09 18:05'],
        ['id'=>32, 'author_alias'=>'María C.',   'text'=>'¿Cuándo repararán los baches de la av. Circunvalación Norte?',                  'channel'=>'web_form',        'polarity'=>'neutral', 'score'=>0.03,'confidence'=>0.76,'topics'=>['obras_publicas','transporte'],'captured_at'=>'2026-05-09 10:48'],
        ['id'=>33, 'author_alias'=>'Pedro S.',   'text'=>'Las aguas residuales están saliendo a la calle en el sector 5.',                'channel'=>'csv_upload',      'polarity'=>'negative','score'=>-0.89,'confidence'=>0.94,'topics'=>['medio_ambiente','obras_publicas'],'captured_at'=>'2026-05-09 07:15'],
        ['id'=>34, 'author_alias'=>'Sofía A.',   'text'=>'Me atendieron muy bien en el área de orientación al vecino.',                   'channel'=>'web_form',        'polarity'=>'positive','score'=>0.79,'confidence'=>0.85,'topics'=>['salud'],'captured_at'=>'2026-05-08 14:40'],
        ['id'=>35, 'author_alias'=>'Marco T.',   'text'=>'¿Cuándo instalarán el semáforo peatonal en la escuela Grau?',                   'channel'=>'public_chatbot',  'polarity'=>'neutral', 'score'=>0.02,'confidence'=>0.73,'topics'=>['transporte','seguridad'],'captured_at'=>'2026-05-08 08:20'],
        ['id'=>36, 'author_alias'=>'Diana L.',   'text'=>'Gracias por arreglar el desagüe del mercado central, ya no hay mal olor.',      'channel'=>'meta_instagram',  'polarity'=>'positive','score'=>0.82,'confidence'=>0.88,'topics'=>['limpieza','obras_publicas'],'captured_at'=>'2026-05-07 16:00'],
        ['id'=>37, 'author_alias'=>'Rosa N.',    'text'=>'Las tuberías de agua en el jr. Huancayo están rotas.',                           'channel'=>'csv_upload',      'polarity'=>'negative','score'=>-0.84,'confidence'=>0.92,'topics'=>['obras_publicas','medio_ambiente'],'captured_at'=>'2026-05-07 09:30'],
        ['id'=>38, 'author_alias'=>'Carmen Q.',  'text'=>'¿Hay becas municipales para estudios técnicos?',                                'channel'=>'public_chatbot',  'polarity'=>'neutral', 'score'=>0.01,'confidence'=>0.72,'topics'=>['educacion'],'captured_at'=>'2026-05-07 11:45'],
        ['id'=>39, 'author_alias'=>'Luis D.',    'text'=>'Muy bien el mantenimiento de la plaza La Merced.',                              'channel'=>'meta_facebook',   'polarity'=>'positive','score'=>0.83,'confidence'=>0.89,'topics'=>['obras_publicas','medio_ambiente'],'captured_at'=>'2026-05-06 15:30'],
        ['id'=>40, 'author_alias'=>'Miguel B.',  'text'=>'Los perros callejeros en el sector 2 son un peligro.',                          'channel'=>'meta_instagram',  'polarity'=>'negative','score'=>-0.74,'confidence'=>0.85,'topics'=>['seguridad','salud'],'captured_at'=>'2026-05-06 10:15'],
        ['id'=>41, 'author_alias'=>'José F.',    'text'=>'Excelente obra de veredas en el jr. Lima, muy necesaria.',                      'channel'=>'web_form',        'polarity'=>'positive','score'=>0.85,'confidence'=>0.90,'topics'=>['obras_publicas'],'captured_at'=>'2026-05-05 14:00'],
        ['id'=>42, 'author_alias'=>'Anónimo',    'text'=>'¿Cuándo abren inscripciones para el programa Vaso de Leche?',                  'channel'=>'public_chatbot',  'polarity'=>'neutral', 'score'=>0.02,'confidence'=>0.74,'topics'=>['salud'],'captured_at'=>'2026-05-05 09:00'],
        ['id'=>43, 'author_alias'=>'Juan P.',    'text'=>'El camión de basura no pasó por mi calle esta semana.',                         'channel'=>'meta_facebook',   'polarity'=>'negative','score'=>-0.77,'confidence'=>0.87,'topics'=>['limpieza'],'captured_at'=>'2026-05-04 18:50'],
        ['id'=>44, 'author_alias'=>'Andrea V.',  'text'=>'La pista de atletismo del coliseo está en excelente estado.',                   'channel'=>'meta_instagram',  'polarity'=>'positive','score'=>0.87,'confidence'=>0.92,'topics'=>['obras_publicas','educacion'],'captured_at'=>'2026-05-04 11:25'],
        ['id'=>45, 'author_alias'=>'María C.',   'text'=>'Piden coima en el área de licencias, hay que investigar.',                     'channel'=>'web_form',        'polarity'=>'negative','score'=>-0.93,'confidence'=>0.97,'topics'=>['seguridad','tributos'],'captured_at'=>'2026-05-03 16:40'],
        ['id'=>46, 'author_alias'=>'Carlos M.',  'text'=>'¿Cuál es el número de emergencias del serenazgo?',                             'channel'=>'public_chatbot',  'polarity'=>'neutral', 'score'=>0.00,'confidence'=>0.71,'topics'=>['seguridad'],'captured_at'=>'2026-05-03 08:55'],
        ['id'=>47, 'author_alias'=>'Sofía A.',   'text'=>'Gracias por el apoyo en el desastre del sector 7, llegaron rápido.',           'channel'=>'meta_facebook',   'polarity'=>'positive','score'=>0.91,'confidence'=>0.95,'topics'=>['seguridad','salud'],'captured_at'=>'2026-05-02 20:00'],
        ['id'=>48, 'author_alias'=>'Pedro S.',   'text'=>'Hay quema de basura ilegal cerca del río, muy contaminante.',                  'channel'=>'csv_upload',      'polarity'=>'negative','score'=>-0.86,'confidence'=>0.92,'topics'=>['medio_ambiente','limpieza'],'captured_at'=>'2026-05-02 15:10'],
    ],
]);

$filteredComments = computed(function () {
    $now = new \DateTime();
    return array_filter($this->allComments, function ($c) use ($now) {
        if ($this->search) {
            $q = mb_strtolower($this->search);
            if (!str_contains(mb_strtolower($c['text']), $q) && !str_contains(mb_strtolower($c['author_alias']), $q)) {
                return false;
            }
        }
        if ($this->filterPolarity && $c['polarity'] !== $this->filterPolarity) {
            return false;
        }
        if ($this->filterChannel && $c['channel'] !== $this->filterChannel) {
            return false;
        }
        if ($this->filterTopic && !in_array($this->filterTopic, $c['topics'])) {
            return false;
        }
        if ($this->filterDays) {
            $captured = new \DateTime($c['captured_at']);
            $diff = $now->diff($captured)->days;
            if ($diff > (int) $this->filterDays) {
                return false;
            }
        }
        return true;
    });
});

$stats = computed(function () {
    $filtered = array_values($this->filteredComments);
    $total = count($filtered);
    if ($total === 0) {
        return ['total' => 0, 'pct_positive' => 0, 'pct_neutral' => 0, 'pct_negative' => 0];
    }
    $positive = count(array_filter($filtered, fn($c) => $c['polarity'] === 'positive'));
    $neutral   = count(array_filter($filtered, fn($c) => $c['polarity'] === 'neutral'));
    $negative  = count(array_filter($filtered, fn($c) => $c['polarity'] === 'negative'));
    return [
        'total'        => $total,
        'pct_positive' => round($positive * 100 / $total),
        'pct_neutral'  => round($neutral * 100 / $total),
        'pct_negative' => round($negative * 100 / $total),
    ];
});

$paginatedComments = computed(function () {
    $all = array_values($this->filteredComments);
    return array_slice($all, ($this->page - 1) * $this->perPage, $this->perPage);
});

$totalPages = computed(function () {
    $total = count($this->filteredComments);
    return max(1, (int) ceil($total / $this->perPage));
});

$clearFilters = function () {
    $this->search = '';
    $this->filterPolarity = '';
    $this->filterChannel = '';
    $this->filterTopic = '';
    $this->filterDays = '';
    $this->page = 1;
};

$setPage = function (int $p) {
    $this->page = max(1, min($p, $this->totalPages));
};

$updatedPerPage = function () {
    $this->page = 1;
};

$updatedSearch = function () { $this->page = 1; };
$updatedFilterPolarity = function () { $this->page = 1; };
$updatedFilterChannel = function () { $this->page = 1; };
$updatedFilterTopic = function () { $this->page = 1; };
$updatedFilterDays = function () { $this->page = 1; };

?>

<div>
    {{-- Page header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="font-serif text-2xl font-bold text-ink-deep">Comentarios ciudadanos</h1>
            <p class="mt-1 text-sm text-ink-soft">Bandeja unificada de canales digitales</p>
        </div>
        <div class="flex items-center gap-2 text-ink-soft">
            <x-icon name="message-square" class="w-5 h-5 text-brand-canopy" />
            <span class="text-xs font-medium uppercase tracking-wide text-brand-canopy">Centro de comentarios</span>
        </div>
    </div>

    {{-- Stats row --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="card flex items-center gap-4">
            <div class="w-10 h-10 rounded-full bg-brand-canopy/10 flex items-center justify-center flex-shrink-0">
                <x-icon name="message-square" class="w-5 h-5 text-brand-canopy" />
            </div>
            <div>
                <p class="text-xs text-ink-soft font-medium uppercase tracking-wide">Total</p>
                <p class="text-2xl font-bold text-ink-deep font-serif">{{ $this->stats['total'] }}</p>
            </div>
        </div>
        <div class="card flex items-center gap-4">
            <div class="w-10 h-10 rounded-full bg-brand-river/10 flex items-center justify-center flex-shrink-0">
                <span class="text-brand-river font-bold text-sm">+</span>
            </div>
            <div>
                <p class="text-xs text-ink-soft font-medium uppercase tracking-wide">Positivos</p>
                <p class="text-2xl font-bold text-brand-river font-serif">{{ $this->stats['pct_positive'] }}%</p>
            </div>
        </div>
        <div class="card flex items-center gap-4">
            <div class="w-10 h-10 rounded-full bg-ink-soft/10 flex items-center justify-center flex-shrink-0">
                <span class="text-ink-soft font-bold text-sm">~</span>
            </div>
            <div>
                <p class="text-xs text-ink-soft font-medium uppercase tracking-wide">Neutrales</p>
                <p class="text-2xl font-bold text-ink-soft font-serif">{{ $this->stats['pct_neutral'] }}%</p>
            </div>
        </div>
        <div class="card flex items-center gap-4">
            <div class="w-10 h-10 rounded-full bg-brand-clay/10 flex items-center justify-center flex-shrink-0">
                <span class="text-brand-clay font-bold text-sm">−</span>
            </div>
            <div>
                <p class="text-xs text-ink-soft font-medium uppercase tracking-wide">Negativos</p>
                <p class="text-2xl font-bold text-brand-clay font-serif">{{ $this->stats['pct_negative'] }}%</p>
            </div>
        </div>
    </div>

    {{-- Filters bar --}}
    <div class="card mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
            {{-- Search --}}
            <div class="lg:col-span-2 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <x-icon name="search" class="w-4 h-4 text-ink-soft" />
                </div>
                <input
                    type="text"
                    wire:model.live.debounce.400ms="search"
                    placeholder="Buscar comentario o autor…"
                    class="w-full pl-9 pr-3 py-2 text-sm border border-brand-mist rounded-lg bg-white text-ink-deep placeholder-ink-soft focus:outline-none focus:ring-2 focus:ring-brand-canopy/40 focus:border-brand-canopy"
                />
            </div>

            {{-- Polarity --}}
            <div>
                <select wire:model.live="filterPolarity" class="w-full py-2 px-3 text-sm border border-brand-mist rounded-lg bg-white text-ink-deep focus:outline-none focus:ring-2 focus:ring-brand-canopy/40 focus:border-brand-canopy">
                    <option value="">Todos los sentimientos</option>
                    <option value="positive">Positivo</option>
                    <option value="neutral">Neutral</option>
                    <option value="negative">Negativo</option>
                </select>
            </div>

            {{-- Channel --}}
            <div>
                <select wire:model.live="filterChannel" class="w-full py-2 px-3 text-sm border border-brand-mist rounded-lg bg-white text-ink-deep focus:outline-none focus:ring-2 focus:ring-brand-canopy/40 focus:border-brand-canopy">
                    <option value="">Todos los canales</option>
                    <option value="meta_facebook">Facebook</option>
                    <option value="meta_instagram">Instagram</option>
                    <option value="web_form">Buzón web</option>
                    <option value="csv_upload">CSV</option>
                    <option value="public_chatbot">Chatbot</option>
                </select>
            </div>

            {{-- Topic --}}
            <div>
                <select wire:model.live="filterTopic" class="w-full py-2 px-3 text-sm border border-brand-mist rounded-lg bg-white text-ink-deep focus:outline-none focus:ring-2 focus:ring-brand-canopy/40 focus:border-brand-canopy">
                    <option value="">Todos los temas</option>
                    <option value="obras_publicas">Obras públicas</option>
                    <option value="seguridad">Seguridad ciudadana</option>
                    <option value="limpieza">Limpieza pública</option>
                    <option value="salud">Salud</option>
                    <option value="tributos">Tributos</option>
                    <option value="educacion">Educación</option>
                    <option value="transporte">Transporte</option>
                    <option value="medio_ambiente">Medio ambiente</option>
                </select>
            </div>

            {{-- Days + Clear --}}
            <div class="flex gap-2">
                <select wire:model.live="filterDays" class="flex-1 py-2 px-3 text-sm border border-brand-mist rounded-lg bg-white text-ink-deep focus:outline-none focus:ring-2 focus:ring-brand-canopy/40 focus:border-brand-canopy">
                    <option value="">Cualquier fecha</option>
                    <option value="7">Últimos 7 días</option>
                    <option value="30">Últimos 30 días</option>
                    <option value="90">Últimos 90 días</option>
                </select>
                <button
                    wire:click="clearFilters"
                    class="px-3 py-2 text-xs font-medium text-ink-soft border border-brand-mist rounded-lg bg-white hover:bg-brand-mist hover:text-ink-deep transition-colors whitespace-nowrap"
                    title="Limpiar filtros"
                >
                    Limpiar
                </button>
            </div>
        </div>
    </div>

    {{-- Table --}}
    @php
        $topicLabels = [
            'obras_publicas' => 'Obras públicas',
            'seguridad'      => 'Seguridad',
            'limpieza'       => 'Limpieza',
            'salud'          => 'Salud',
            'tributos'       => 'Tributos',
            'educacion'      => 'Educación',
            'transporte'     => 'Transporte',
            'medio_ambiente' => 'Medio ambiente',
        ];
        $channelConfig = [
            'meta_facebook'  => ['label' => 'Facebook',   'class' => 'bg-brand-river/10 text-brand-river'],
            'meta_instagram' => ['label' => 'Instagram',  'class' => 'bg-brand-gold/10 text-brand-gold'],
            'web_form'       => ['label' => 'Buzón web',  'class' => 'bg-brand-canopy/10 text-brand-canopy'],
            'csv_upload'     => ['label' => 'CSV',        'class' => 'bg-brand-clay/10 text-brand-clay'],
            'public_chatbot' => ['label' => 'Chatbot',    'class' => 'bg-ink-soft/10 text-ink-soft'],
        ];
        $polarityConfig = [
            'positive' => ['label' => 'Positivo', 'class' => 'bg-brand-river/10 text-brand-river'],
            'neutral'  => ['label' => 'Neutral',  'class' => 'bg-ink-soft/10 text-ink-soft'],
            'negative' => ['label' => 'Negativo', 'class' => 'bg-brand-clay/10 text-brand-clay'],
        ];
        $paginated = $this->paginatedComments;
        $total     = $this->stats['total'];
        $totalPgs  = $this->totalPages;
    @endphp

    @if ($total === 0)
        <div class="card text-center py-16">
            <div class="w-16 h-16 mx-auto bg-brand-canopy/10 rounded-full flex items-center justify-center text-brand-canopy mb-4">
                <x-icon name="message-square" class="w-8 h-8" />
            </div>
            <h3 class="font-serif text-lg font-semibold text-ink-deep">Sin resultados</h3>
            <p class="mt-2 text-sm text-ink-soft max-w-sm mx-auto">No se encontraron comentarios con los filtros aplicados.</p>
            <button wire:click="clearFilters" class="mt-5 btn-primary">Limpiar filtros</button>
        </div>
    @else
        <div class="card overflow-hidden p-0">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-brand-mist border-b border-brand-mist">
                        <tr>
                            <th class="px-4 py-3 text-xs font-semibold text-ink-soft uppercase tracking-wider w-8">#</th>
                            <th class="px-4 py-3 text-xs font-semibold text-ink-soft uppercase tracking-wider">Autor</th>
                            <th class="px-4 py-3 text-xs font-semibold text-ink-soft uppercase tracking-wider">Texto</th>
                            <th class="px-4 py-3 text-xs font-semibold text-ink-soft uppercase tracking-wider">Canal</th>
                            <th class="px-4 py-3 text-xs font-semibold text-ink-soft uppercase tracking-wider">Sentimiento</th>
                            <th class="px-4 py-3 text-xs font-semibold text-ink-soft uppercase tracking-wider">Temas</th>
                            <th class="px-4 py-3 text-xs font-semibold text-ink-soft uppercase tracking-wider">Captura</th>
                            <th class="px-4 py-3 text-xs font-semibold text-ink-soft uppercase tracking-wider text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-mist">
                        @foreach ($paginated as $idx => $comment)
                            @php
                                $rowNum = ($this->page - 1) * $this->perPage + $idx + 1;
                                $ch     = $channelConfig[$comment['channel']] ?? ['label' => $comment['channel'], 'class' => 'bg-ink-soft/10 text-ink-soft'];
                                $pol    = $polarityConfig[$comment['polarity']] ?? ['label' => $comment['polarity'], 'class' => 'bg-ink-soft/10 text-ink-soft'];
                            @endphp
                            <tr class="hover:bg-brand-mist/50 transition-colors">
                                <td class="px-4 py-3 text-xs text-ink-soft font-mono">{{ $rowNum }}</td>
                                <td class="px-4 py-3">
                                    <span class="font-medium text-ink-deep">{{ $comment['author_alias'] }}</span>
                                </td>
                                <td class="px-4 py-3 max-w-xs">
                                    <div class="relative group">
                                        <span class="block truncate text-ink-deep" title="{{ $comment['text'] }}">
                                            {{ $comment['text'] }}
                                        </span>
                                        <div class="absolute bottom-full left-0 mb-2 hidden group-hover:block z-10 w-72 bg-ink-deep text-white text-xs rounded-lg px-3 py-2 shadow-brand-lg leading-relaxed pointer-events-none">
                                            {{ $comment['text'] }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $ch['class'] }}">
                                        {{ $ch['label'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $pol['class'] }}">
                                        {{ $pol['label'] }}
                                    </span>
                                    <div class="text-xs text-ink-soft mt-0.5">{{ number_format($comment['score'], 2) }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach ($comment['topics'] as $slug)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-brand-canopy/10 text-brand-canopy">
                                                {{ $topicLabels[$slug] ?? $slug }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-xs text-ink-soft whitespace-nowrap">
                                    {{ \Illuminate\Support\Carbon::parse($comment['captured_at'])->format('d/m/Y') }}<br>
                                    <span class="text-ink-soft/60">{{ \Illuminate\Support\Carbon::parse($comment['captured_at'])->format('H:i') }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-1">
                                        <button
                                            class="p-1.5 rounded-lg text-ink-soft hover:text-brand-river hover:bg-brand-river/10 transition-colors"
                                            title="Re-analizar"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                            </svg>
                                        </button>
                                        <button
                                            class="p-1.5 rounded-lg text-ink-soft hover:text-brand-canopy hover:bg-brand-canopy/10 transition-colors"
                                            title="Ver detalle"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination footer --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-4 py-3 border-t border-brand-mist bg-brand-mist/30">
                <div class="flex items-center gap-2 text-sm text-ink-soft">
                    <span>Mostrando</span>
                    <select wire:model.live="perPage" class="py-1 px-2 text-xs border border-brand-mist rounded bg-white text-ink-deep focus:outline-none focus:ring-1 focus:ring-brand-canopy/40">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                    <span>de <strong class="text-ink-deep">{{ $total }}</strong> comentarios</span>
                </div>
                <div class="flex items-center gap-1">
                    <button
                        wire:click="setPage({{ $this->page - 1 }})"
                        @disabled($this->page <= 1)
                        class="px-3 py-1.5 text-xs font-medium rounded-lg border border-brand-mist bg-white text-ink-soft hover:bg-brand-mist hover:text-ink-deep transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                    >
                        Anterior
                    </button>
                    @for ($p = max(1, $this->page - 2); $p <= min($totalPgs, $this->page + 2); $p++)
                        <button
                            wire:click="setPage({{ $p }})"
                            class="px-3 py-1.5 text-xs font-medium rounded-lg border transition-colors {{ $p === $this->page ? 'bg-brand-canopy text-white border-brand-canopy' : 'border-brand-mist bg-white text-ink-soft hover:bg-brand-mist' }}"
                        >
                            {{ $p }}
                        </button>
                    @endfor
                    <button
                        wire:click="setPage({{ $this->page + 1 }})"
                        @disabled($this->page >= $totalPgs)
                        class="px-3 py-1.5 text-xs font-medium rounded-lg border border-brand-mist bg-white text-ink-soft hover:bg-brand-mist hover:text-ink-deep transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                    >
                        Siguiente
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
