<?php

use function Livewire\Volt\title;

title('Chat RAG interno · SIIM');

?>

<div>
    <div class="card text-center py-16">
        <div class="w-20 h-20 mx-auto bg-brand-canopy/10 rounded-full flex items-center justify-center text-brand-canopy mb-4">
            <x-icon name="brain" class="w-10 h-10" />
        </div>
        <h2 class="font-serif text-xl font-semibold text-brand-canopy">Chat RAG interno</h2>
        <p class="mt-2 text-sm text-ink-soft max-w-md mx-auto">Pregunta en lenguaje natural sobre los datos analizados — F6.</p>
        <a href="/panel" class="mt-6 inline-flex btn-primary">Volver al dashboard</a>
    </div>
</div>
