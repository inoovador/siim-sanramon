<x-layouts.public title="Demasiadas solicitudes · SIIM">
    <div class="mx-auto w-full max-w-3xl px-4 py-12 sm:px-6 sm:py-16">
        <section class="rounded-2xl border border-brand-canopy/10 bg-white p-6 text-center shadow-brand sm:p-10" aria-labelledby="rate-limit-title">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-brand-gold/20 text-2xl" aria-hidden="true">⏳</div>
            <h1 id="rate-limit-title" class="mt-5 text-2xl font-bold text-brand-canopy">Demasiadas solicitudes</h1>
            <p class="mx-auto mt-3 max-w-lg text-sm leading-6 text-ink-soft">
                Recibimos muchas solicitudes desde tu conexión en poco tiempo. Espera unos segundos y vuelve a intentarlo.
            </p>
            <div class="mt-6 flex flex-col items-center justify-center gap-3 sm:flex-row">
                <a href="{{ route('survey.show') }}" class="btn-primary min-h-11 w-full justify-center sm:w-auto">Volver a la encuesta</a>
                <a href="{{ url('/') }}" class="inline-flex min-h-11 w-full items-center justify-center rounded-lg border border-brand-canopy px-4 py-2 text-sm font-semibold text-brand-canopy transition hover:bg-brand-canopy/5 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-gold sm:w-auto">Ir al inicio</a>
            </div>
        </section>
    </div>
</x-layouts.public>
