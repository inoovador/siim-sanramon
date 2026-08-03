<?php

declare(strict_types=1);

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use App\Http\Exceptions\SurveySubmissionRateLimitException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use SIIM\Application\Citizen\Commands\SubmitSurveyResponseCommand;
use SIIM\Application\Citizen\UseCases\GetPublishedSurveyUseCase;
use SIIM\Application\Citizen\UseCases\SubmitSurveyResponseUseCase;
use SIIM\Application\Citizen\UseCases\TrackSurveyAttemptUseCase;
use SIIM\Domain\Citizen\Exceptions\DuplicateResponseException;
use SIIM\Domain\Citizen\Exceptions\SurveyClosedException;
use SIIM\Domain\Citizen\QuestionType;
use SIIM\Domain\Citizen\Survey;
use SIIM\Domain\Citizen\SurveyQuestion;
use SIIM\Infrastructure\Security\SurveySubmissionRateLimitKey;

new #[Layout('components.layouts.public')] #[Title('Encuesta ciudadana · SIIM')] class extends Component
{
    /** @var array{id: string, slug: string, title: string, description: ?string, questions: list<array<string, mixed>>} */
    #[Locked]
    public array $survey = [];

    /** @var array<string, mixed> */
    public array $answers = [];

    #[Locked]
    public string $attemptId = '';

    #[Locked]
    public bool $available = false;

    public string $website = '';

    public ?string $formError = null;

    public function mount(GetPublishedSurveyUseCase $getSurvey, TrackSurveyAttemptUseCase $tracking): void
    {
        $slug = trim((string) config('citizen.public_survey_slug', ''));
        if ($slug === '') {
            return;
        }

        try {
            $now = now()->toDateTimeImmutable();
            $survey = $getSurvey->handle($slug, $now);
        } catch (InvalidArgumentException|SurveyClosedException) {
            return;
        }

        $this->survey = $this->surveyData($survey);
        $this->answers = $this->initialAnswers($survey);
        $this->attemptId = $tracking->start($survey->id, $now);
        $this->available = true;
    }

    #[Computed]
    public function progress(): int
    {
        if (! $this->available) {
            return 0;
        }

        $required = array_values(array_filter(
            $this->survey['questions'],
            static fn (array $question): bool => (bool) $question['is_required'],
        ));
        if ($required === []) {
            return 100;
        }

        $answered = 0;
        foreach ($required as $question) {
            $value = $this->answers[(string) $question['id']] ?? null;
            if ((is_array($value) && $value !== []) || (! is_array($value) && $value !== null && trim((string) $value) !== '')) {
                $answered++;
            }
        }

        return (int) floor(($answered / count($required)) * 100);
    }

    public function submit(
        SubmitSurveyResponseUseCase $submitResponse,
        TrackSurveyAttemptUseCase $tracking,
        SurveySubmissionRateLimitKey $rateLimitKey,
    ): mixed {
        $this->formError = null;
        if (! $this->available || $this->attemptId === '') {
            $this->formError = 'La encuesta no está disponible en este momento.';

            return null;
        }

        $ipAddress = request()->ip() ?? 'unknown';
        $key = $rateLimitKey->forIp($ipAddress);
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $tracking->discard($this->attemptId, (string) $this->survey['id']);
            throw new SurveySubmissionRateLimitException();
        }
        RateLimiter::hit($key, 60);

        $submittedAt = now()->toDateTimeImmutable();
        $elapsedMilliseconds = $tracking->elapsedMilliseconds(
            $this->attemptId,
            (string) $this->survey['id'],
            $submittedAt,
        );

        if (trim($this->website) !== '' || ($elapsedMilliseconds !== null && $elapsedMilliseconds < 5000)) {
            $tracking->discard($this->attemptId, (string) $this->survey['id']);
            session()->flash('survey_confirmation_generic', true);

            return $this->redirectRoute('survey.thanks');
        }

        if ($elapsedMilliseconds === null) {
            $this->formError = 'La sesión de la encuesta venció. Recarga la página para intentarlo nuevamente.';

            return null;
        }

        $validated = $this->validate($this->rules(), $this->messages());
        /** @var array<string, mixed> $validatedAnswers */
        $validatedAnswers = $validated['answers'];

        try {
            $result = $submitResponse->handle(new SubmitSurveyResponseCommand(
                surveySlug: (string) $this->survey['slug'],
                answersByQuestionId: $this->normalizeAnswers($validatedAnswers),
                ipAddress: $ipAddress,
                userAgent: request()->userAgent(),
                completionMs: $elapsedMilliseconds,
                submittedAt: $submittedAt,
                attemptId: $this->attemptId,
            ));
        } catch (DuplicateResponseException) {
            $tracking->discard($this->attemptId, (string) $this->survey['id']);
            $this->formError = 'Ya registramos una respuesta desde esta conexión hoy. Gracias por participar.';

            return null;
        } catch (SurveyClosedException) {
            $tracking->discard($this->attemptId, (string) $this->survey['id']);
            $this->formError = 'Esta encuesta ya no recibe respuestas.';

            return null;
        } catch (Throwable $exception) {
            report($exception);
            $this->formError = 'No pudimos guardar tu respuesta. Inténtalo nuevamente en unos minutos.';

            return null;
        }

        $confirmation = strtoupper(substr(str_replace('-', '', $result->responseId), 0, 8));
        session()->flash('survey_confirmation_code', $confirmation);

        return $this->redirectRoute('survey.thanks');
    }

    /** @return array<string, list<mixed>> */
    protected function rules(): array
    {
        $rules = ['answers' => ['required', 'array']];

        foreach ($this->survey['questions'] as $question) {
            $id = (string) $question['id'];
            $key = "answers.{$id}";
            $required = (bool) $question['is_required'];
            $type = (string) $question['type'];

            if ($type === QuestionType::Scale1To5->value) {
                $rules[$key] = [$required ? 'required' : 'nullable', 'integer', 'between:1,5'];
            } elseif ($type === QuestionType::Nps->value) {
                $rules[$key] = [$required ? 'required' : 'nullable', 'integer', 'between:0,10'];
            } elseif ($type === QuestionType::SingleChoice->value) {
                $rules[$key] = [$required ? 'required' : 'nullable', 'string', Rule::in($this->optionValues($question))];
            } elseif ($type === QuestionType::MultiChoice->value) {
                $maxSelections = (int) ($question['max_selections'] ?? 0);
                $rules[$key] = [$required ? 'required' : 'nullable', 'array', 'max:' . $maxSelections];
                $rules["{$key}.*"] = ['string', 'distinct', Rule::in($this->optionValues($question))];
            } else {
                $maxLength = (int) ($question['max_length'] ?? 1000);
                $rules[$key] = [$required ? 'required' : 'nullable', 'string', 'max:' . $maxLength];
            }
        }

        return $rules;
    }

    /** @return array<string, string> */
    protected function messages(): array
    {
        return [
            'required' => 'Este campo es obligatorio.',
            'integer' => 'Selecciona una opción válida.',
            'between' => 'Selecciona un valor dentro del rango permitido.',
            'in' => 'Selecciona una de las opciones disponibles.',
            'array' => 'Selecciona opciones válidas.',
            'distinct' => 'No repitas una misma opción.',
            'max' => 'Has excedido el límite permitido para esta respuesta.',
            'string' => 'Ingresa un texto válido.',
        ];
    }

    /** @return array{id: string, slug: string, title: string, description: ?string, questions: list<array<string, mixed>>} */
    private function surveyData(Survey $survey): array
    {
        return [
            'id' => $survey->id,
            'slug' => $survey->slug,
            'title' => $survey->title,
            'description' => $survey->description,
            'questions' => array_map(fn (SurveyQuestion $question): array => [
                'id' => $question->id,
                'position' => $question->position,
                'type' => $question->type->value,
                'label' => $question->label,
                'help_text' => $question->helpText,
                'is_required' => $question->isRequired,
                'options' => $this->displayOptions($question),
                'max_selections' => $question->maxSelections,
                'max_length' => $question->maxLength,
            ], $survey->questions),
        ];
    }

    /** @return array<string, mixed> */
    private function initialAnswers(Survey $survey): array
    {
        $answers = [];
        foreach ($survey->questions as $question) {
            $answers[$question->id] = $question->type === QuestionType::MultiChoice ? [] : '';
        }

        return $answers;
    }

    /** @return list<array{value: int|string, label: string}> */
    private function displayOptions(SurveyQuestion $question): array
    {
        $options = $question->options;
        if ($options === [] && $question->type === QuestionType::Scale1To5) {
            $options = range(1, 5);
        } elseif ($options === [] && $question->type === QuestionType::Nps) {
            $options = range(0, 10);
        }

        return array_map(static function (string|array|int $option): array {
            if (is_array($option)) {
                return [
                    'value' => $option['value'],
                    'label' => (string) ($option['label'] ?? $option['value']),
                ];
            }

            return ['value' => $option, 'label' => (string) $option];
        }, $options);
    }

    /** @param array<string, mixed> $question
     *  @return list<string>
     */
    private function optionValues(array $question): array
    {
        /** @var list<array{value: int|string, label: string}> $options */
        $options = $question['options'];

        return array_map(static fn (array $option): string => (string) $option['value'], $options);
    }

    /** @param array<string, mixed> $answers
     *  @return array<string, mixed>
     */
    private function normalizeAnswers(array $answers): array
    {
        $normalized = [];
        foreach ($this->survey['questions'] as $question) {
            $id = (string) $question['id'];
            if (! array_key_exists($id, $answers)) {
                continue;
            }

            $value = $answers[$id];
            $type = (string) $question['type'];
            if ($type === QuestionType::Scale1To5->value || $type === QuestionType::Nps->value) {
                $normalized[$id] = (int) $value;
            } elseif (is_array($value)) {
                $normalized[$id] = array_values(array_map(static fn (mixed $item): string => (string) $item, $value));
            } else {
                $normalized[$id] = trim((string) $value);
            }
        }

        return $normalized;
    }
};

?>

<div class="mx-auto w-full max-w-3xl px-4 py-8 sm:px-6 sm:py-12">
    @if (! $available)
        <section class="rounded-2xl border border-brand-canopy/10 bg-white p-6 text-center shadow-brand sm:p-10" aria-labelledby="survey-unavailable-title">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-brand-gold/20 text-2xl" aria-hidden="true">⌛</div>
            <h1 id="survey-unavailable-title" class="mt-5 text-2xl font-bold text-brand-canopy">Encuesta no disponible</h1>
            <p class="mx-auto mt-3 max-w-lg text-sm leading-6 text-ink-soft">En este momento no hay una encuesta ciudadana activa. Vuelve a visitarnos pronto.</p>
            <a href="{{ url('/') }}" class="mt-6 inline-flex rounded-lg border border-brand-canopy px-4 py-2 text-sm font-semibold text-brand-canopy transition hover:bg-brand-canopy/5 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-gold">Volver al inicio</a>
        </section>
    @else
        <header class="mb-7 text-center sm:mb-9">
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-brand-river">Tu voz mejora San Ramón</p>
            <h1 class="mt-3 text-3xl font-bold leading-tight text-brand-canopy sm:text-4xl">{{ $survey['title'] }}</h1>
            @if ($survey['description'])
                <p class="mx-auto mt-3 max-w-2xl text-sm leading-6 text-ink-soft">{{ $survey['description'] }}</p>
            @else
                <p class="mx-auto mt-3 max-w-2xl text-sm leading-6 text-ink-soft">Completa este formulario anónimo. Tus respuestas nos ayudarán a priorizar mejores servicios municipales.</p>
            @endif
        </header>

        <div class="sticky top-0 z-10 mb-6 rounded-xl border border-brand-canopy/10 bg-white/95 p-4 shadow-brand backdrop-blur" aria-live="polite">
            <div class="mb-2 flex items-center justify-between gap-4 text-xs font-semibold">
                <span class="text-ink-soft">Progreso de preguntas obligatorias</span>
                <span class="text-brand-canopy">{{ $this->progress }}%</span>
            </div>
            <div class="h-2 overflow-hidden rounded-full bg-brand-mist" role="progressbar" aria-label="Progreso de la encuesta" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $this->progress }}">
                <div class="h-full rounded-full bg-brand-river transition-all duration-300" style="width: {{ $this->progress }}%"></div>
            </div>
        </div>

        <form wire:submit="submit" class="space-y-5" novalidate>
            <div class="absolute -left-[10000px] top-auto h-px w-px overflow-hidden" aria-hidden="true">
                <label for="website">Sitio web</label>
                <input id="website" type="text" wire:model="website" tabindex="-1" autocomplete="off">
            </div>

            @foreach ($survey['questions'] as $question)
                @php($answerKey = 'answers.' . $question['id'])
                <fieldset class="rounded-2xl border border-brand-canopy/10 bg-white p-5 shadow-brand sm:p-6" wire:key="question-{{ $question['id'] }}">
                    <legend class="w-full px-1 text-base font-semibold leading-6 text-ink-deep">
                        <span class="mr-2 text-brand-river">{{ $question['position'] }}.</span>{{ $question['label'] }}
                        @if ($question['is_required'])
                            <span class="ml-1 text-brand-clay" aria-label="obligatorio">*</span>
                        @else
                            <span class="ml-2 text-xs font-normal text-ink-soft">Opcional</span>
                        @endif
                    </legend>

                    @if ($question['help_text'])
                        <p id="help-{{ $question['id'] }}" class="mt-2 text-sm text-ink-soft">{{ $question['help_text'] }}</p>
                    @endif

                    @if (in_array($question['type'], ['single_choice', 'scale_1_5', 'nps'], true))
                        <div class="mt-4 grid gap-2 {{ $question['type'] === 'nps' ? 'grid-cols-4 sm:grid-cols-11' : ($question['type'] === 'scale_1_5' ? 'grid-cols-2 sm:grid-cols-5' : 'grid-cols-1 sm:grid-cols-2') }}">
                            @foreach ($question['options'] as $option)
                                <label for="answer-{{ $question['id'] }}-{{ $loop->index }}" class="flex min-h-11 cursor-pointer items-center gap-2 rounded-lg border border-brand-canopy/20 px-3 py-2 text-sm text-ink-deep transition hover:border-brand-river hover:bg-brand-mist focus-within:outline focus-within:outline-2 focus-within:outline-offset-2 focus-within:outline-brand-gold">
                                    <input id="answer-{{ $question['id'] }}-{{ $loop->index }}" type="radio" wire:model.live="answers.{{ $question['id'] }}" value="{{ $option['value'] }}" class="border-brand-canopy/40 text-brand-canopy focus:ring-brand-gold">
                                    <span>{{ $option['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        @if ($question['type'] === 'nps')
                            <div class="mt-2 flex justify-between text-xs text-ink-soft"><span>Nada probable</span><span>Muy probable</span></div>
                        @endif
                    @elseif ($question['type'] === 'multi_choice')
                        <p class="mt-2 text-xs text-ink-soft">Puedes elegir hasta {{ $question['max_selections'] }} opciones.</p>
                        <div class="mt-4 grid gap-2 sm:grid-cols-2">
                            @foreach ($question['options'] as $option)
                                <label for="answer-{{ $question['id'] }}-{{ $loop->index }}" class="flex min-h-11 cursor-pointer items-center gap-3 rounded-lg border border-brand-canopy/20 px-3 py-2 text-sm text-ink-deep transition hover:border-brand-river hover:bg-brand-mist focus-within:outline focus-within:outline-2 focus-within:outline-offset-2 focus-within:outline-brand-gold">
                                    <input id="answer-{{ $question['id'] }}-{{ $loop->index }}" type="checkbox" wire:model.live="answers.{{ $question['id'] }}" value="{{ $option['value'] }}" class="rounded border-brand-canopy/40 text-brand-canopy focus:ring-brand-gold">
                                    <span>{{ $option['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    @else
                        <label for="answer-{{ $question['id'] }}" class="sr-only">{{ $question['label'] }}</label>
                        <textarea id="answer-{{ $question['id'] }}" wire:model.blur="answers.{{ $question['id'] }}" rows="{{ $question['max_length'] > 200 ? 5 : 2 }}" maxlength="{{ $question['max_length'] }}" class="mt-4 block w-full rounded-lg border-brand-canopy/20 bg-brand-mist/40 text-sm text-ink-deep placeholder:text-ink-soft/70 focus:border-brand-river focus:ring-brand-river" placeholder="Escribe tu respuesta aquí"></textarea>
                        <p class="mt-2 text-right text-xs text-ink-soft">Máximo {{ $question['max_length'] }} caracteres</p>
                    @endif

                    @error($answerKey)
                        <p class="mt-3 text-sm font-semibold text-brand-clay" role="alert">{{ $message }}</p>
                    @enderror
                    @error($answerKey . '.*')
                        <p class="mt-3 text-sm font-semibold text-brand-clay" role="alert">{{ $message }}</p>
                    @enderror
                </fieldset>
            @endforeach

            <aside class="rounded-xl border border-brand-river/20 bg-brand-river/5 p-4 text-sm leading-6 text-ink-soft" aria-label="Aviso de privacidad">
                <h2 class="font-serif text-base font-semibold text-brand-canopy">Privacidad y protección de datos</h2>
                <p class="mt-1">De acuerdo con la Ley N.º 29733, el dato de contacto opcional se almacena cifrado y su acceso está restringido. No guardamos tu IP; usamos una huella diaria protegida para evitar respuestas duplicadas.</p>
            </aside>

            @if ($formError)
                <div class="rounded-xl border border-brand-clay/30 bg-brand-clay/10 p-4 text-sm font-semibold text-brand-clay" role="alert" aria-live="assertive">
                    {{ $formError }}
                </div>
            @endif

            <button type="submit" class="btn-primary min-h-12 w-full justify-center text-base disabled:cursor-not-allowed disabled:opacity-60" wire:loading.attr="disabled" wire:target="submit">
                <span wire:loading.remove wire:target="submit">Enviar mis respuestas</span>
                <span wire:loading.inline-flex wire:target="submit" class="items-center gap-2" aria-live="polite">
                    <span class="h-4 w-4 animate-spin rounded-full border-2 border-white/40 border-t-white" aria-hidden="true"></span>
                    Guardando respuesta…
                </span>
            </button>
        </form>
    @endif
</div>
