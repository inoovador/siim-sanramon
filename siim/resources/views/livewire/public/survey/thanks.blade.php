<?php

declare(strict_types=1);

use function Livewire\Volt\layout;
use function Livewire\Volt\state;
use function Livewire\Volt\title;

layout('components.layouts.public');
title('Gracias por participar · SIIM');

state(['confirmationCode' => fn () => session('survey_confirmation_code')]);

?>

<div class="mx-auto max-w-2xl px-4 py-12 text-center sm:px-6 sm:py-20">
    <section class="rounded-2xl border border-brand-canopy/10 bg-white p-7 shadow-brand-lg sm:p-12" aria-labelledby="thanks-title">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-brand-canopy text-3xl text-white" aria-hidden="true">✓</div>
        <p class="mt-6 text-xs font-bold uppercase tracking-[0.18em] text-brand-river">Participación ciudadana</p>
        <h1 id="thanks-title" class="mt-2 text-3xl font-bold text-brand-canopy">Gracias por participar</h1>
        <p class="mx-auto mt-4 max-w-lg text-sm leading-6 text-ink-soft">Tu opinión será incorporada al análisis de percepción ciudadana para orientar mejores decisiones municipales.</p>

        @if (is_string($confirmationCode) && $confirmationCode !== '')
            <div class="mx-auto mt-6 max-w-xs rounded-xl bg-brand-mist p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-ink-soft">Código de confirmación</p>
                <p class="mt-1 font-mono text-xl font-bold tracking-widest text-brand-canopy">{{ $confirmationCode }}</p>
            </div>
        @else
            <p class="mx-auto mt-6 max-w-lg rounded-xl bg-brand-mist p-4 text-sm text-ink-soft">Tu participación contribuye a mejorar San Ramón.</p>
        @endif

        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
            <a href="{{ url('/') }}" class="btn-primary justify-center">Volver al inicio</a>
            <a href="{{ route('survey.show') }}" class="inline-flex min-h-10 items-center justify-center rounded-lg border border-brand-canopy px-4 py-2 text-sm font-semibold text-brand-canopy transition hover:bg-brand-canopy/5 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-gold">Ver la encuesta</a>
        </div>
    </section>
</div>
