<x-layouts.app title="Nueva encuesta" :breadcrumb="['Panel', 'Encuestas', 'Nueva']">
    <form method="POST" action="{{ route('panel.surveys.store') }}" class="mx-auto max-w-4xl space-y-6" x-data="surveyBuilder({{ Illuminate\Support\Js::from($questionTypes) }})">
        @csrf
        <section class="card grid gap-4 md:grid-cols-2">
            <label class="text-sm font-semibold text-ink-deep">Título
                <input name="title" value="{{ old('title') }}" required maxlength="200" class="mt-1 w-full rounded-lg border-brand-canopy/20 focus:border-brand-river focus:ring-brand-river" />
            </label>
            <label class="text-sm font-semibold text-ink-deep">Slug
                <input name="slug" value="{{ old('slug') }}" required maxlength="64" pattern="[a-z0-9]+(?:-[a-z0-9]+)*" placeholder="servicios-2026" class="mt-1 w-full rounded-lg border-brand-canopy/20 focus:border-brand-river focus:ring-brand-river" />
            </label>
            <label class="md:col-span-2 text-sm font-semibold text-ink-deep">Descripción
                <textarea name="description" maxlength="2000" rows="3" class="mt-1 w-full rounded-lg border-brand-canopy/20 focus:border-brand-river focus:ring-brand-river">{{ old('description') }}</textarea>
            </label>
            <label class="text-sm font-semibold text-ink-deep">Apertura opcional
                <input name="opens_at" type="datetime-local" value="{{ old('opens_at') }}" class="mt-1 w-full rounded-lg border-brand-canopy/20 focus:border-brand-river focus:ring-brand-river" />
            </label>
            <label class="text-sm font-semibold text-ink-deep">Cierre opcional
                <input name="closes_at" type="datetime-local" value="{{ old('closes_at') }}" class="mt-1 w-full rounded-lg border-brand-canopy/20 focus:border-brand-river focus:ring-brand-river" />
            </label>
        </section>

        @if($errors->any())
            <div class="rounded-lg border border-brand-clay/30 bg-brand-clay/10 px-4 py-3 text-sm text-brand-clay" role="alert">
                <ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <section class="space-y-4" aria-labelledby="questions-title">
            <div class="flex items-center justify-between gap-3">
                <h2 id="questions-title" class="font-serif text-xl font-semibold text-brand-canopy">Preguntas</h2>
                <button type="button" @click="addQuestion" class="rounded-lg border border-brand-canopy px-3 py-2 text-sm font-semibold text-brand-canopy hover:bg-brand-canopy hover:text-white focus:outline-none focus:ring-2 focus:ring-brand-gold">Agregar pregunta</button>
            </div>
            <template x-for="(question, index) in questions" :key="question.key">
                <article class="card">
                    <div class="flex items-center justify-between gap-3">
                        <h3 class="font-serif font-semibold text-brand-canopy">Pregunta <span x-text="index + 1"></span></h3>
                        <button type="button" @click="removeQuestion(index)" :disabled="questions.length === 1" class="text-sm font-semibold text-brand-clay underline disabled:cursor-not-allowed disabled:opacity-40">Quitar</button>
                    </div>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <label class="md:col-span-2 text-sm font-semibold text-ink-deep">Texto
                            <input :name="`questions[${index}][label]`" x-model="question.label" required maxlength="300" class="mt-1 w-full rounded-lg border-brand-canopy/20 focus:border-brand-river focus:ring-brand-river" />
                        </label>
                        <label class="text-sm font-semibold text-ink-deep">Tipo
                            <select :name="`questions[${index}][type]`" x-model="question.type" @change="typeChanged(question)" class="mt-1 w-full rounded-lg border-brand-canopy/20 focus:border-brand-river focus:ring-brand-river">
                                <template x-for="type in types" :key="type.value"><option :value="type.value" x-text="type.label"></option></template>
                            </select>
                        </label>
                        <label class="flex items-center gap-2 self-end pb-3 text-sm text-ink-deep">
                            <input type="hidden" :name="`questions[${index}][is_required]`" value="0" />
                            <input type="checkbox" :name="`questions[${index}][is_required]`" value="1" x-model="question.required" class="rounded border-brand-canopy/30 text-brand-canopy focus:ring-brand-river" /> Obligatoria
                        </label>
                        <label x-show="requiresOptions(question.type)" class="md:col-span-2 text-sm font-semibold text-ink-deep">Opciones (una por línea)
                            <textarea :name="`questions[${index}][options]`" x-model="question.options" rows="4" class="mt-1 w-full rounded-lg border-brand-canopy/20 focus:border-brand-river focus:ring-brand-river"></textarea>
                        </label>
                        <label x-show="question.type === 'multi_choice'" class="text-sm font-semibold text-ink-deep">Máximo de selecciones
                            <input :name="`questions[${index}][max_selections]`" x-model="question.maxSelections" type="number" min="1" max="100" class="mt-1 w-full rounded-lg border-brand-canopy/20 focus:border-brand-river focus:ring-brand-river" />
                        </label>
                        <label x-show="question.type === 'open_text'" class="text-sm font-semibold text-ink-deep">Máximo de caracteres
                            <input :name="`questions[${index}][max_length]`" x-model="question.maxLength" type="number" min="1" max="5000" class="mt-1 w-full rounded-lg border-brand-canopy/20 focus:border-brand-river focus:ring-brand-river" />
                        </label>
                    </div>
                </article>
            </template>
        </section>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <a href="{{ route('panel.surveys') }}" class="rounded-lg border border-brand-canopy/20 px-5 py-2.5 text-center text-sm font-semibold text-ink-soft hover:text-brand-canopy">Cancelar</a>
            <button type="submit" class="rounded-lg bg-brand-canopy px-5 py-2.5 text-sm font-semibold text-white shadow-brand hover:bg-brand-river focus:outline-none focus:ring-2 focus:ring-brand-gold">Guardar borrador</button>
        </div>
    </form>

    @push('scripts')
        <script>
            function surveyBuilder(types) {
                return {
                    types,
                    nextKey: 2,
                    questions: [{key: 1, label: '', type: types[0]?.value ?? 'scale_1_5', required: true, options: '', maxSelections: '', maxLength: ''}],
                    addQuestion() { this.questions = [...this.questions, {key: this.nextKey++, label: '', type: this.types[0]?.value ?? 'scale_1_5', required: true, options: '', maxSelections: '', maxLength: ''}]; },
                    removeQuestion(index) { if (this.questions.length > 1) this.questions = this.questions.filter((_, itemIndex) => itemIndex !== index); },
                    requiresOptions(value) { return this.types.find(type => type.value === value)?.requiresOptions ?? false; },
                    typeChanged(question) {
                        if (!this.requiresOptions(question.type)) question.options = '';
                        question.maxSelections = question.type === 'multi_choice' ? (question.maxSelections || 1) : '';
                        question.maxLength = question.type === 'open_text' ? (question.maxLength || 1000) : '';
                    },
                };
            }
        </script>
    @endpush
</x-layouts.app>
