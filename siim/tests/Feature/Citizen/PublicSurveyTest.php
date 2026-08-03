<?php

declare(strict_types=1);

use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Models\SurveyAttempt;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use Carbon\CarbonImmutable;
use Database\Seeders\SurveySeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Testing\TestResponse;
use Livewire\Volt\Volt;
use SIIM\Application\Citizen\Contracts\CitizenSubmissionRepository;
use SIIM\Application\Citizen\Data\SurveySubmission;
use SIIM\Application\Citizen\UseCases\GetPublishedSurveyUseCase;
use SIIM\Domain\Citizen\Events\CommentIngested;
use SIIM\Domain\Citizen\Exceptions\SurveyClosedException;
use SIIM\Domain\Citizen\SurveyStatus;
use SIIM\Infrastructure\Security\SurveySubmissionRateLimitKey;
use Tests\TestCase;

beforeEach(function (): void {
    config(['citizen.public_survey_slug' => 'percepcion-2026']);
    app(SurveySeeder::class)->run();
    $appKey = config('app.key');
    assert(is_string($appKey));
    RateLimiter::clear('survey-submit:' . hash_hmac('sha256', '127.0.0.1', $appKey));
    Event::fake([CommentIngested::class]);
});

/** @return array<string, mixed> */
function publicSurveyAnswers(): array
{
    $survey = Survey::query()->where('slug', 'percepcion-2026')->with('questions')->firstOrFail();
    $answers = [];
    foreach ($survey->questions as $question) {
        $options = $question->options ?? [];
        $value = match ($question->position) {
            1, 2 => publicSurveyOptionValue($options[0] ?? null),
            3, 4, 5, 6, 7, 8 => '4',
            9 => '9',
            10 => array_map(publicSurveyOptionValue(...), array_slice($options, 0, 2)),
            11 => 'La atención fue buena y oportuna.',
            12 => 'vecina@example.test',
            default => '',
        };

        $answers[(string) $question->id] = $value;
    }

    return $answers;
}

/** @param string|array{value: int|string, label?: string}|null $option */
function publicSurveyOptionValue(string|array|null $option): string
{
    if (is_array($option)) {
        return (string) $option['value'];
    }

    return $option ?? '';
}

it('serves the public survey and thanks routes anonymously with GET throttling', function (): void {
    /** @var TestCase $this */
    $this->get('/encuesta')
        ->assertOk()
        ->assertSee('Encuesta de Percepción Ciudadana');

    $this->get('/encuesta/gracias')
        ->assertOk()
        ->assertSee('Gracias por participar');

    foreach (['survey.show', 'survey.thanks'] as $routeName) {
        $route = Route::getRoutes()->getByName($routeName);
        expect($route)->not->toBeNull()
            ->and($route?->gatherMiddleware())->toContain('throttle:20,1');
    }
});

it('renders questions from the configured published survey and starts a private anonymous attempt', function (): void {
    $survey = Survey::query()->where('slug', 'percepcion-2026')->with('questions')->firstOrFail();

    $component = Volt::test('public.survey.show');

    foreach ($survey->questions as $question) {
        $component->assertSee($question->label);
    }

    $attempt = SurveyAttempt::query()->sole();
    expect($attempt->survey_id)->toBe($survey->id)
        ->and($attempt->started_at)->not->toBeNull()
        ->and($attempt->completed_at)->toBeNull()
        ->and($attempt->response_id)->toBeNull()
        ->and(array_keys($attempt->getAttributes()))->not->toContain('ip_address', 'ip_hash', 'user_agent', 'user_agent_hash', 'contact');
});

it('validates required, configured, range, selection and text rules in Spanish', function (): void {
    /** @var TestCase $this */
    $survey = Survey::query()->where('slug', 'percepcion-2026')->with('questions')->firstOrFail();
    $byPosition = $survey->questions->keyBy('position');
    $answers = publicSurveyAnswers();
    $answers[(string) $byPosition->get(3)?->id] = '6';
    $answers[(string) $byPosition->get(10)?->id] = ['obras_publicas', 'seguridad', 'salud', 'limpieza'];
    $answers[(string) $byPosition->get(11)?->id] = str_repeat('a', 1001);
    unset($answers[(string) $byPosition->get(1)?->id]);

    $component = Volt::test('public.survey.show');
    $this->travel(5)->seconds();
    $component
        ->set('answers', $answers)
        ->call('submit')
        ->assertHasErrors([
            'answers.' . $byPosition->get(1)?->id,
            'answers.' . $byPosition->get(3)?->id,
            'answers.' . $byPosition->get(10)?->id,
            'answers.' . $byPosition->get(11)?->id,
        ])
        ->assertSee('Este campo es obligatorio.');

    expect(SurveyResponse::query()->count())->toBe(0);
});

it('normalizes numeric answers, persists a response and completes the same attempt', function (): void {
    /** @var TestCase $this */
    $component = Volt::test('public.survey.show');
    $attemptId = $component->get('attemptId');
    $this->travel(5)->seconds();

    $component
        ->set('answers', publicSurveyAnswers())
        ->call('submit')
        ->assertHasNoErrors()
        ->assertRedirect(route('survey.thanks'));

    $response = SurveyResponse::query()->sole();
    $attempt = SurveyAttempt::query()->findOrFail($attemptId);
    assert($attempt instanceof SurveyAttempt);
    $numericAnswers = SurveyAnswer::query()->where('response_id', $response->id)->whereNotNull('value_int')->pluck('value_int');

    expect($response->completion_ms)->toBeGreaterThanOrEqual(5000)
        ->and($attempt->response_id)->toBe($response->id)
        ->and($attempt->completed_at)->not->toBeNull()
        ->and($numericAnswers)->toHaveCount(7)
        ->and($numericAnswers->every(static fn (mixed $value): bool => is_int($value)))->toBeTrue();
});

it('accepts an omitted optional comment and shows a non-sensitive confirmation', function (): void {
    /** @var TestCase $this */
    $survey = Survey::query()->where('slug', 'percepcion-2026')->with('questions')->firstOrFail();
    $commentQuestion = $survey->questions->firstWhere('position', 11);
    assert($commentQuestion instanceof SurveyQuestion);
    $answers = publicSurveyAnswers();
    unset($answers[(string) $commentQuestion->id]);

    $component = Volt::test('public.survey.show');
    $this->travel(5)->seconds();
    $component->set('answers', $answers)->call('submit')->assertRedirect(route('survey.thanks'));

    $response = SurveyResponse::query()->sole();
    $this->get('/encuesta/gracias')
        ->assertOk()
        ->assertSee(strtoupper(substr(str_replace('-', '', (string) $response->id), 0, 8)))
        ->assertDontSee((string) $response->id);
});

it('silently discards honeypot and too-fast submissions without polluting attempts', function (string $trap, int $elapsedSeconds): void {
    /** @var TestCase $this */
    $component = Volt::test('public.survey.show');
    if ($elapsedSeconds > 0) {
        $this->travel($elapsedSeconds)->seconds();
    }

    $component
        ->set('answers', publicSurveyAnswers())
        ->set('website', $trap)
        ->call('submit')
        ->assertRedirect(route('survey.thanks'));

    expect(SurveyResponse::query()->count())->toBe(0)
        ->and(SurveyAttempt::query()->count())->toBe(0);
})->with([
    'honeypot' => ['https://spam.example', 5],
    'too fast' => ['', 4],
]);

it('shows a soft duplicate message and removes its attempt from the denominator', function (): void {
    /** @var TestCase $this */
    $first = Volt::test('public.survey.show');
    $this->travel(5)->seconds();
    $first->set('answers', publicSurveyAnswers())->call('submit')->assertRedirect(route('survey.thanks'));

    $second = Volt::test('public.survey.show');
    $this->travel(5)->seconds();
    $second
        ->set('answers', publicSurveyAnswers())
        ->call('submit')
        ->assertNoRedirect()
        ->assertSee('Ya registramos una respuesta desde esta conexión hoy.');

    expect(SurveyResponse::query()->count())->toBe(1)
        ->and(SurveyAttempt::query()->count())->toBe(1);
});

it('returns a real Spanish HTTP 429 on the sixth submit action', function (): void {
    /** @var TestCase $this */
    $component = Volt::test('public.survey.show');
    $this->travel(5)->seconds();

    for ($attempt = 1; $attempt <= 5; $attempt++) {
        $component->call('submit')->assertHasErrors();
    }

    $component->call('submit')->assertStatus(429);
    $response = (fn (): mixed => $this->lastState->getResponse())->call($component);
    assert($response instanceof TestResponse);
    expect($response->getContent())->toBe('Has realizado demasiados intentos. Espera un minuto e inténtalo nuevamente.')
        ->and($response->headers->get('Retry-After'))->toBe('60');

    expect(SurveyAttempt::query()->count())->toBe(0);
});

it('links the public survey from the landing page', function (): void {
    /** @var TestCase $this */
    $this->get('/')
        ->assertOk()
        ->assertSee(route('survey.show'), escape: false)
        ->assertSee('Participar en la encuesta');
});

it('fails closed with a public state when configuration is absent or the survey is not published', function (string $configuredSlug, bool $makeDraft): void {
    /** @var TestCase $this */
    if ($makeDraft) {
        Survey::query()->where('slug', 'percepcion-2026')->update(['status' => SurveyStatus::Draft]);
    }
    config(['citizen.public_survey_slug' => $configuredSlug]);

    $this->get('/encuesta')
        ->assertOk()
        ->assertSee('Encuesta no disponible')
        ->assertDontSee('Enviar mis respuestas');

    expect(SurveyAttempt::query()->count())->toBe(0);
})->with([
    'missing configuration' => ['', false],
    'draft configured survey' => ['percepcion-2026', true],
    'unknown configured survey' => ['encuesta-inexistente', false],
]);

it('shows a generic Spanish persistence error without leaking exception details', function (): void {
    /** @var TestCase $this */
    $component = Volt::test('public.survey.show');
    $this->travel(5)->seconds();
    app()->bind(CitizenSubmissionRepository::class, fn (): CitizenSubmissionRepository => new class implements CitizenSubmissionRepository
    {
        public function existsForDate(string $surveyId, string $ipHash, DateTimeImmutable $date): bool
        {
            return false;
        }

        public function save(SurveySubmission $submission): never
        {
            throw new RuntimeException('internal-secret-database-detail');
        }
    });

    $component
        ->set('answers', publicSurveyAnswers())
        ->call('submit')
        ->assertNoRedirect()
        ->assertSee('No pudimos guardar tu respuesta. Inténtalo nuevamente en unos minutos.')
        ->assertDontSee('internal-secret-database-detail');

    expect(SurveyResponse::query()->count())->toBe(0)
        ->and(SurveyAttempt::query()->whereNotNull('completed_at')->count())->toBe(0);
});

it('uses an HMAC rate-limit key that never contains the raw IP address', function (): void {
    $ipAddress = '198.51.100.47';
    $appKey = 'test-rate-limit-secret';
    $key = (new SurveySubmissionRateLimitKey($appKey))->forIp($ipAddress);

    expect($key)->toBe('survey-submit:' . hash_hmac('sha256', $ipAddress, $appKey))
        ->and($key)->not->toContain($ipAddress);
});

it('keeps persistence and seeded survey content out of the public Volt view', function (): void {
    $view = file_get_contents(resource_path('views/livewire/public/survey/show.blade.php'));
    expect($view)->toBeString()
        ->and($view)->not->toContain('App\\Models', 'DB::', 'percepcion-2026', '¿En qué zona del distrito vives?', 'assertAcceptsResponsesAt');
});

it('keeps microsecond precision and applies the exact five-second submission boundary', function (int $elapsedMilliseconds, bool $accepted): void {
    /** @var TestCase $this */
    $precision = DB::table('information_schema.columns')
        ->whereRaw('table_schema = database()')
        ->where('table_name', 'survey_attempts')
        ->whereIn('column_name', ['started_at', 'completed_at'])
        ->pluck('datetime_precision', 'column_name');
    expect($precision->map(static function (mixed $value): int {
        assert(is_int($value) || is_string($value));

        return (int) $value;
    })->sortKeys()->all())->toBe([
        'completed_at' => 6,
        'started_at' => 6,
    ]);

    $this->travelTo(CarbonImmutable::parse('2026-08-03 10:00:00.100000'));
    $component = Volt::test('public.survey.show');
    $this->travel($elapsedMilliseconds)->milliseconds();
    $component
        ->set('answers', publicSurveyAnswers())
        ->call('submit')
        ->assertRedirect(route('survey.thanks'));

    expect(SurveyResponse::query()->count())->toBe($accepted ? 1 : 0)
        ->and(SurveyAttempt::query()->count())->toBe($accepted ? 1 : 0);
})->with([
    '4.9 seconds is discarded' => [4900, false],
    '5.0 seconds is accepted' => [5000, true],
]);

it('enforces the configured survey response window in the application use case', function (): void {
    $survey = Survey::query()->where('slug', 'percepcion-2026')->firstOrFail();
    $survey->update(['opens_at' => '2026-08-03 10:00:01.000000', 'closes_at' => null]);

    $useCase = app(GetPublishedSurveyUseCase::class);
    expect(fn () => $useCase->handle('percepcion-2026', new DateTimeImmutable('2026-08-03 10:00:00.999999')))
        ->toThrow(SurveyClosedException::class);

    $survey->update(['opens_at' => null, 'closes_at' => '2026-08-03 09:59:59.999999']);
    expect(fn () => $useCase->handle('percepcion-2026', new DateTimeImmutable('2026-08-03 10:00:00.000000')))
        ->toThrow(SurveyClosedException::class);
});
