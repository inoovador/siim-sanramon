<?php

use function Livewire\Volt\state;

state([
    'kpis' => fn () => [
        ['label' => 'Comentarios totales', 'value' => '1,247', 'delta' => '+12%', 'positive' => true, 'icon' => 'message-square'],
        ['label' => 'Sentimiento positivo', 'value' => '64%', 'delta' => '+4 pts', 'positive' => true, 'icon' => 'brain'],
        ['label' => 'Comentarios hoy', 'value' => '23', 'delta' => '-5%', 'positive' => false, 'icon' => 'bar-chart'],
        ['label' => 'Tema trending', 'value' => 'Obras públicas', 'delta' => '38 menciones', 'positive' => true, 'icon' => 'database'],
    ],
    'channelData' => fn () => [
        ['label' => 'Facebook', 'value' => 487, 'color' => '#1E7FA8'],
        ['label' => 'Instagram', 'value' => 312, 'color' => '#E0A24A'],
        ['label' => 'Formulario web', 'value' => 248, 'color' => '#0F4D2A'],
        ['label' => 'CSV importado', 'value' => 145, 'color' => '#9B4A2C'],
        ['label' => 'Chatbot público', 'value' => 55, 'color' => '#5D6A60'],
    ],
    'recent' => fn () => [
        ['alias' => 'Juan P.', 'channel' => 'Facebook', 'text' => 'El pavimento de la av. Marginal está pésimo desde hace meses.', 'polarity' => 'negative', 'when' => 'hace 5 min'],
        ['alias' => 'María C.', 'channel' => 'Instagram', 'text' => 'Gracias por el operativo de seguridad ciudadana, se nota la presencia.', 'polarity' => 'positive', 'when' => 'hace 12 min'],
        ['alias' => 'Anónimo', 'channel' => 'Buzón web', 'text' => '¿Cuándo retoman la recolección de residuos en la calle Junín?', 'polarity' => 'neutral', 'when' => 'hace 28 min'],
        ['alias' => 'Carlos M.', 'channel' => 'Facebook', 'text' => 'La nueva plaza está hermosa, felicitaciones al equipo.', 'polarity' => 'positive', 'when' => 'hace 45 min'],
        ['alias' => 'Lucía R.', 'channel' => 'Chatbot', 'text' => 'No funciona el alumbrado en el jirón Tarma desde el martes.', 'polarity' => 'negative', 'when' => 'hace 1 h'],
        ['alias' => 'Pedro S.', 'channel' => 'Instagram', 'text' => 'Excelente la campaña de salud preventiva.', 'polarity' => 'positive', 'when' => 'hace 1 h'],
        ['alias' => 'Anónimo', 'channel' => 'Buzón web', 'text' => 'Solicito mayor frecuencia en limpieza pública del mercado.', 'polarity' => 'neutral', 'when' => 'hace 2 h'],
        ['alias' => 'Andrea V.', 'channel' => 'Facebook', 'text' => 'Hay un poste a punto de caer en la av. Circunvalación.', 'polarity' => 'negative', 'when' => 'hace 2 h'],
        ['alias' => 'Marco T.', 'channel' => 'Chatbot', 'text' => '¿Dónde puedo consultar mi recibo del impuesto predial?', 'polarity' => 'neutral', 'when' => 'hace 3 h'],
        ['alias' => 'Diana L.', 'channel' => 'Instagram', 'text' => 'Las clases del taller municipal están muy bien organizadas.', 'polarity' => 'positive', 'when' => 'hace 3 h'],
    ],
    'topics' => fn () => [
        ['label' => 'Obras públicas', 'count' => 38, 'trend' => 'up'],
        ['label' => 'Seguridad ciudadana', 'count' => 27, 'trend' => 'up'],
        ['label' => 'Limpieza pública', 'count' => 22, 'trend' => 'down'],
        ['label' => 'Salud', 'count' => 18, 'trend' => 'up'],
        ['label' => 'Tributos', 'count' => 14, 'trend' => 'flat'],
        ['label' => 'Educación', 'count' => 11, 'trend' => 'up'],
        ['label' => 'Transporte', 'count' => 9, 'trend' => 'down'],
        ['label' => 'Medio ambiente', 'count' => 7, 'trend' => 'up'],
    ],
]);

?>

<x-layouts.app title="Dashboard" :breadcrumb="['SIIM', 'Dashboard']">

    {{-- KPI cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach($kpis as $kpi)
            <div class="card">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs uppercase tracking-wider text-ink-soft font-semibold">{{ $kpi['label'] }}</p>
                        <p class="mt-2 text-2xl font-serif font-bold text-brand-canopy">{{ $kpi['value'] }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-lg bg-brand-canopy/10 text-brand-canopy flex items-center justify-center flex-shrink-0">
                        <x-icon :name="$kpi['icon']" class="w-5 h-5" />
                    </div>
                </div>
                <p class="mt-3 text-xs {{ $kpi['positive'] ? 'text-brand-river' : 'text-brand-clay' }} font-medium">
                    {{ $kpi['delta'] }} <span class="text-ink-soft">vs semana pasada</span>
                </p>
            </div>
        @endforeach
    </div>

    {{-- Charts grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        {{-- Line: sentimiento últimos 30 días --}}
        <div class="card lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="font-serif font-semibold text-ink-deep">Sentimiento ciudadano · últimos 30 días</h3>
                    <p class="text-xs text-ink-soft mt-0.5">Tendencia diaria por polaridad</p>
                </div>
                <span class="text-xs px-2 py-1 rounded-full bg-brand-mist text-ink-soft">Datos demo</span>
            </div>
            <div id="chart-sentiment" class="h-72"></div>
        </div>

        {{-- Donut: canales --}}
        <div class="card">
            <h3 class="font-serif font-semibold text-ink-deep mb-1">Distribución por canal</h3>
            <p class="text-xs text-ink-soft mb-4">Comentarios últimos 30 días</p>
            <div id="chart-channel" class="h-72"></div>
        </div>
    </div>

    {{-- Topics trending --}}
    <div class="card mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-serif font-semibold text-ink-deep">Temas trending</h3>
            <a href="/panel/temas" class="text-xs text-brand-river hover:text-brand-canopy underline">Ver vocabulario</a>
        </div>
        <div class="flex flex-wrap gap-2">
            @foreach($topics as $topic)
                @php
                    $trendIcon = match($topic['trend']) {
                        'up' => '↑',
                        'down' => '↓',
                        default => '→',
                    };
                    $trendColor = match($topic['trend']) {
                        'up' => 'text-brand-river',
                        'down' => 'text-brand-clay',
                        default => 'text-ink-soft',
                    };
                @endphp
                <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-brand-mist border border-brand-canopy/10 text-sm">
                    <span class="font-medium text-ink-deep">{{ $topic['label'] }}</span>
                    <span class="text-xs text-ink-soft">{{ $topic['count'] }}</span>
                    <span class="text-xs {{ $trendColor }}">{{ $trendIcon }}</span>
                </span>
            @endforeach
        </div>
    </div>

    {{-- Tabla últimos comentarios --}}
    <div class="card">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-serif font-semibold text-ink-deep">Últimos comentarios analizados</h3>
                <p class="text-xs text-ink-soft mt-0.5">Datos de muestra · F2 conectará la ingesta real</p>
            </div>
            <a href="/panel/comentarios" class="text-xs text-brand-river hover:text-brand-canopy underline">Ver todos</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase tracking-wider text-ink-soft border-b border-brand-canopy/10">
                        <th class="pb-3 pr-4">Autor</th>
                        <th class="pb-3 pr-4">Canal</th>
                        <th class="pb-3 pr-4">Comentario</th>
                        <th class="pb-3 pr-4">Sentimiento</th>
                        <th class="pb-3">Cuándo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-canopy/5">
                    @foreach($recent as $c)
                        @php
                            $polarityClass = match($c['polarity']) {
                                'positive' => 'bg-sentiment-positive/10 text-sentiment-positive',
                                'negative' => 'bg-sentiment-negative/10 text-sentiment-negative',
                                default => 'bg-sentiment-neutral/20 text-ink-soft',
                            };
                            $polarityLabel = match($c['polarity']) {
                                'positive' => 'Positivo',
                                'negative' => 'Negativo',
                                default => 'Neutral',
                            };
                        @endphp
                        <tr class="hover:bg-brand-mist/50">
                            <td class="py-3 pr-4 font-medium text-ink-deep">{{ $c['alias'] }}</td>
                            <td class="py-3 pr-4 text-ink-soft">{{ $c['channel'] }}</td>
                            <td class="py-3 pr-4 text-ink-deep max-w-md truncate">{{ $c['text'] }}</td>
                            <td class="py-3 pr-4">
                                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $polarityClass }}">{{ $polarityLabel }}</span>
                            </td>
                            <td class="py-3 text-ink-soft text-xs whitespace-nowrap">{{ $c['when'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Sentimiento line chart
            new ApexCharts(document.querySelector("#chart-sentiment"), {
                chart: { type: 'area', height: 288, toolbar: { show: false }, fontFamily: 'Inter, sans-serif' },
                series: [
                    { name: 'Positivo', data: [12,18,15,22,19,25,28,24,30,27,33,29,35,32,38,34,40,37,42,39,45,41,47,44,50,46,52,48,54,51] },
                    { name: 'Neutral',  data: [8,10,9,12,11,13,14,12,15,13,16,14,17,15,18,16,19,17,20,18,21,19,22,20,23,21,24,22,25,23] },
                    { name: 'Negativo', data: [5,7,6,8,7,9,10,8,11,9,12,10,13,11,14,12,15,13,16,14,17,15,18,16,19,17,20,18,21,19] },
                ],
                colors: ['#1E7FA8', '#B8B0A0', '#9B4A2C'],
                stroke: { curve: 'smooth', width: 2 },
                fill: { type: 'gradient', gradient: { opacityFrom: 0.3, opacityTo: 0.05 } },
                xaxis: { categories: Array.from({length:30},(_,i)=>`${i+1}`), labels:{ style:{ colors:'#5D6A60' } } },
                yaxis: { labels: { style: { colors: '#5D6A60' } } },
                grid: { borderColor: '#0F4D2A0F' },
                legend: { position: 'top', horizontalAlign: 'right', labels:{ colors:'#1A1F1B' } },
                tooltip: { theme: 'light' },
                dataLabels: { enabled: false },
            }).render();

            // Channel donut
            new ApexCharts(document.querySelector("#chart-channel"), {
                chart: { type: 'donut', height: 288, fontFamily: 'Inter, sans-serif' },
                series: [487, 312, 248, 145, 55],
                labels: ['Facebook', 'Instagram', 'Formulario web', 'CSV importado', 'Chatbot público'],
                colors: ['#1E7FA8', '#E0A24A', '#0F4D2A', '#9B4A2C', '#5D6A60'],
                legend: { position: 'bottom', labels:{ colors:'#1A1F1B' } },
                plotOptions: { pie: { donut: { size: '60%', labels: { show: true, total: { show: true, label: 'Total', color: '#0F4D2A', fontFamily: 'Source Serif Pro' } } } } },
                dataLabels: { enabled: false },
                stroke: { width: 0 },
            }).render();
        });
    </script>
    @endpush
</x-layouts.app>
