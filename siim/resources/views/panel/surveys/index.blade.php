<x-layouts.app title="Encuestas" :breadcrumb="['Panel', 'Encuestas']">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-ink-soft">Resultados y participación de las encuestas ciudadanas.</p>
        </div>
        @if(auth()->user()?->hasRole('admin'))
            <a href="{{ route('panel.surveys.create') }}" class="inline-flex items-center justify-center rounded-lg bg-brand-canopy px-4 py-2.5 text-sm font-semibold text-white shadow-brand transition hover:bg-brand-river focus:outline-none focus:ring-2 focus:ring-brand-gold">
                Nueva encuesta
            </a>
        @endif
    </div>

    @if($surveys === [])
        <div class="card mt-6 text-center">
            <img src="/images/logo-siim.png" alt="" class="mx-auto h-16 w-auto opacity-40" />
            <h2 class="mt-4 font-serif text-xl font-semibold text-brand-canopy">Aún no hay encuestas</h2>
            <p class="mt-2 text-sm text-ink-soft">Crea un borrador para comenzar a recoger respuestas.</p>
        </div>
    @else
        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach($surveys as $survey)
                <article class="card flex flex-col">
                    <div class="flex items-start justify-between gap-3">
                        <h2 class="font-serif text-lg font-semibold text-brand-canopy">{{ $survey->title }}</h2>
                        <span class="rounded-full bg-brand-mist px-2.5 py-1 text-xs font-semibold uppercase tracking-wide text-ink-soft">{{ match($survey->status) { 'published' => 'Publicada', 'closed' => 'Cerrada', default => 'Borrador' } }}</span>
                    </div>
                    <dl class="mt-5 grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-ink-soft">Respuestas</dt>
                            <dd class="mt-1 text-xl font-bold text-ink-deep">{{ number_format($survey->responseCount) }}</dd>
                        </div>
                        <div>
                            <dt class="text-ink-soft">Finalización</dt>
                            <dd class="mt-1 text-xl font-bold text-ink-deep">{{ $survey->completionRate === null ? 'N/D' : number_format($survey->completionRate, 2).'%' }}</dd>
                        </div>
                    </dl>
                    <p class="mt-4 text-xs text-ink-soft">
                        Última respuesta: {{ $survey->lastResponseAt?->format('d/m/Y H:i') ?? 'Sin respuestas' }}
                    </p>
                    <a href="{{ route('panel.surveys.results', ['slug' => $survey->slug]) }}" class="mt-5 inline-flex items-center justify-center rounded-lg border border-brand-canopy px-4 py-2 text-sm font-semibold text-brand-canopy transition hover:bg-brand-canopy hover:text-white focus:outline-none focus:ring-2 focus:ring-brand-gold">
                        Ver resultados
                    </a>
                </article>
            @endforeach
        </div>
    @endif
</x-layouts.app>
