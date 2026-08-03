<x-layouts.app :title="$results->title" :breadcrumb="['Panel', 'Encuestas', $results->title]">
    @if(session('status'))
        <div class="mb-5 rounded-lg border border-brand-river/30 bg-brand-river/10 px-4 py-3 text-sm text-brand-canopy" role="status">{{ session('status') }}</div>
    @endif

    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <form method="GET" action="{{ route('panel.surveys.results', ['slug' => $results->slug]) }}" class="grid flex-1 gap-3 rounded-xl border border-brand-canopy/10 bg-white p-4 sm:grid-cols-2 xl:grid-cols-5">
            <label class="text-xs font-semibold text-ink-soft">Desde
                <input name="from" type="date" value="{{ $filters->from?->format('Y-m-d') }}" class="mt-1 w-full rounded-lg border-brand-canopy/20 text-sm focus:border-brand-river focus:ring-brand-river" />
            </label>
            <label class="text-xs font-semibold text-ink-soft">Hasta
                <input name="to" type="date" value="{{ $filters->to?->format('Y-m-d') }}" class="mt-1 w-full rounded-lg border-brand-canopy/20 text-sm focus:border-brand-river focus:ring-brand-river" />
            </label>
            <label class="text-xs font-semibold text-ink-soft">Zona
                <select name="zone" class="mt-1 w-full rounded-lg border-brand-canopy/20 text-sm focus:border-brand-river focus:ring-brand-river">
                    <option value="">Todas</option>
                    @foreach($options->zones as $zone)<option value="{{ $zone }}" @selected($filters->zone === $zone)>{{ $zone }}</option>@endforeach
                </select>
            </label>
            <label class="text-xs font-semibold text-ink-soft">Edad
                <select name="age" class="mt-1 w-full rounded-lg border-brand-canopy/20 text-sm focus:border-brand-river focus:ring-brand-river">
                    <option value="">Todas</option>
                    @foreach($options->ageRanges as $age)<option value="{{ $age }}" @selected($filters->ageRange === $age)>{{ $age }}</option>@endforeach
                </select>
            </label>
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 rounded-lg bg-brand-canopy px-4 py-2 text-sm font-semibold text-white focus:outline-none focus:ring-2 focus:ring-brand-gold">Aplicar</button>
                <a href="{{ route('panel.surveys.results', ['slug' => $results->slug]) }}" class="rounded-lg border border-brand-canopy/20 px-3 py-2 text-sm text-ink-soft hover:text-brand-canopy">Limpiar</a>
            </div>
        </form>
        <a href="{{ route('panel.surveys.export', array_filter(['slug' => $results->slug, 'from' => $filters->from?->format('Y-m-d'), 'to' => $filters->to?->format('Y-m-d'), 'zone' => $filters->zone, 'age' => $filters->ageRange])) }}" class="inline-flex shrink-0 items-center justify-center rounded-lg bg-brand-gold px-4 py-2.5 text-sm font-semibold text-ink-deep focus:outline-none focus:ring-2 focus:ring-brand-canopy">
            Exportar CSV
        </a>
    </div>

    @if($errors->any())
        <div class="mt-4 rounded-lg border border-brand-clay/30 bg-brand-clay/10 px-4 py-3 text-sm text-brand-clay" role="alert">{{ $errors->first() }}</div>
    @endif

    <section class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Indicadores">
        @foreach([
            ['label' => 'Respuestas', 'value' => number_format($results->totalResponses)],
            ['label' => 'Promedio general', 'value' => $results->generalAverage === null ? 'N/D' : number_format($results->generalAverage, 2)],
            ['label' => 'NPS', 'value' => $results->nps === null ? 'N/D' : number_format($results->nps, 2)],
            ['label' => 'Sentimiento positivo', 'value' => $results->positiveSentimentPercent === null ? 'N/D' : number_format($results->positiveSentimentPercent, 2).'%'],
        ] as $kpi)
            <div class="card">
                <p class="text-xs font-semibold uppercase tracking-wider text-ink-soft">{{ $kpi['label'] }}</p>
                <p class="mt-2 font-serif text-3xl font-bold text-brand-canopy">{{ $kpi['value'] }}</p>
            </div>
        @endforeach
    </section>

    <section class="mt-6 grid gap-4 xl:grid-cols-2" aria-labelledby="scale-title">
        <h2 id="scale-title" class="sr-only">Distribuciones de escala</h2>
        @forelse($results->scales as $scale)
            <article class="card">
                <div class="flex items-start justify-between gap-3">
                    <h3 class="font-serif font-semibold text-brand-canopy">{{ $scale->label }}</h3>
                    <span class="rounded-full bg-brand-river/10 px-2.5 py-1 text-sm font-semibold text-brand-river">{{ $scale->average === null ? 'N/D' : number_format($scale->average, 2) }}</span>
                </div>
                <div class="mt-4 space-y-2">
                    @foreach($scale->buckets as $value => $count)
                        @php($percent = $scale->answers === 0 ? 0 : 100 * $count / $scale->answers)
                        <div class="grid grid-cols-[1.5rem_1fr_2.5rem] items-center gap-2 text-xs">
                            <span class="font-semibold text-ink-soft">{{ $value }}</span>
                            <div class="h-2 overflow-hidden rounded-full bg-brand-mist"><div class="h-full rounded-full bg-brand-river" style="width: {{ $percent }}%"></div></div>
                            <span class="text-right text-ink-soft">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
            </article>
        @empty
            <div class="card text-sm text-ink-soft">Esta encuesta no tiene preguntas de escala.</div>
        @endforelse
    </section>

    <section class="card mt-6" aria-labelledby="priority-title">
        <h2 id="priority-title" class="font-serif text-lg font-semibold text-brand-canopy">Prioridades ciudadanas</h2>
        <div class="mt-4 space-y-3">
            @forelse($results->priorities as $priority)
                <div class="flex items-center justify-between gap-4 border-b border-brand-canopy/5 pb-3 text-sm">
                    <span class="text-ink-deep">{{ $priority->label }}</span>
                    <span class="font-semibold text-brand-canopy">{{ $priority->count }} · {{ number_format($priority->percent, 2) }}%</span>
                </div>
            @empty
                <p class="text-sm text-ink-soft">Sin respuestas de prioridades.</p>
            @endforelse
        </div>
    </section>

    <section class="card mt-6" aria-labelledby="comments-title">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h2 id="comments-title" class="font-serif text-lg font-semibold text-brand-canopy">Comentarios abiertos</h2>
                <p class="text-xs text-ink-soft">{{ $results->comments->total }} comentarios encontrados</p>
            </div>
            <form method="GET" action="{{ route('panel.surveys.results', ['slug' => $results->slug]) }}" class="flex gap-2">
                @foreach(['from' => $filters->from?->format('Y-m-d'), 'to' => $filters->to?->format('Y-m-d'), 'zone' => $filters->zone, 'age' => $filters->ageRange] as $name => $value)
                    @if($value !== null)<input type="hidden" name="{{ $name }}" value="{{ $value }}" />@endif
                @endforeach
                <label class="sr-only" for="survey-comment-search">Buscar comentarios</label>
                <input id="survey-comment-search" name="search" value="{{ $filters->search }}" maxlength="120" placeholder="Buscar comentario" class="min-w-0 rounded-lg border-brand-canopy/20 text-sm focus:border-brand-river focus:ring-brand-river" />
                <button class="rounded-lg bg-brand-canopy px-3 py-2 text-sm font-semibold text-white focus:outline-none focus:ring-2 focus:ring-brand-gold">Buscar</button>
            </form>
        </div>

        <div class="mt-4 space-y-3">
            @forelse($results->comments->items as $comment)
                <article class="rounded-lg border border-brand-canopy/10 bg-brand-mist/30 p-4">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $comment->polarity === 'positive' ? 'bg-sentiment-positive/10 text-sentiment-positive' : ($comment->polarity === 'negative' ? 'bg-sentiment-negative/10 text-sentiment-negative' : 'bg-sentiment-neutral/20 text-ink-soft') }}">
                            {{ match($comment->polarity) { 'positive' => 'Positivo', 'negative' => 'Negativo', 'neutral' => 'Neutral', default => 'Pendiente' } }}
                        </span>
                        <time class="text-xs text-ink-soft">{{ $comment->submittedAt->format('d/m/Y H:i') }}</time>
                    </div>
                    <p class="mt-3 whitespace-pre-wrap text-sm text-ink-deep">{{ $comment->text }}</p>
                    @if(auth()->user()?->hasRole('admin') && $comment->contactProvided)
                        <div class="mt-3" x-data="{
                            loading: false,
                            contact: '',
                            error: '',
                            async reveal() {
                                this.loading = true; this.error = '';
                                try {
                                    const response = await fetch({{ Illuminate\Support\Js::from(route('panel.surveys.contact', ['slug' => $results->slug, 'response' => $comment->responseId])) }}, {
                                        method: 'POST',
                                        headers: {'X-CSRF-TOKEN': {{ Illuminate\Support\Js::from(csrf_token()) }}, 'Accept': 'application/json'},
                                    });
                                    if (!response.ok) throw new Error('request-failed');
                                    const payload = await response.json();
                                    this.contact = payload.contact;
                                } catch (_) { this.error = 'No se pudo revelar el contacto.'; }
                                finally { this.loading = false; }
                            }
                        }">
                            <button type="button" @click="reveal" :disabled="loading || contact !== ''" class="text-xs font-semibold text-brand-river underline focus:outline-none focus:ring-2 focus:ring-brand-gold disabled:opacity-50">
                                <span x-show="!loading">Revelar contacto</span><span x-show="loading">Consultando…</span>
                            </button>
                            <p x-show="contact" x-text="contact" class="mt-2 rounded bg-white px-3 py-2 text-sm text-ink-deep" aria-live="polite"></p>
                            <p x-show="error" x-text="error" class="mt-2 text-xs text-brand-clay" role="alert"></p>
                        </div>
                    @endif
                </article>
            @empty
                <p class="py-8 text-center text-sm text-ink-soft">No hay comentarios para estos filtros.</p>
            @endforelse
        </div>

        @if($results->comments->lastPage > 1)
            <nav class="mt-5 flex items-center justify-between text-sm" aria-label="Paginación de comentarios">
                @if($results->comments->currentPage > 1)<a class="text-brand-river underline" href="{{ request()->fullUrlWithQuery(['page' => $results->comments->currentPage - 1]) }}">Anterior</a>@else<span></span>@endif
                <span class="text-ink-soft">Página {{ $results->comments->currentPage }} de {{ $results->comments->lastPage }}</span>
                @if($results->comments->currentPage < $results->comments->lastPage)<a class="text-brand-river underline" href="{{ request()->fullUrlWithQuery(['page' => $results->comments->currentPage + 1]) }}">Siguiente</a>@else<span></span>@endif
            </nav>
        @endif
    </section>
</x-layouts.app>
