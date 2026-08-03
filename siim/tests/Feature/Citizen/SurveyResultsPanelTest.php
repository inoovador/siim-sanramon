<?php

declare(strict_types=1);

use App\Http\SurveyFilterFactory;
use App\Models\Comment;
use App\Models\SentimentScore;
use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Models\SurveyAttempt;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Database\Seeders\SurveySeeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SIIM\Application\Citizen\Contracts\ContactEncryptor;
use SIIM\Application\Citizen\Contracts\SurveyContactAccessRepository;
use SIIM\Application\Citizen\Queries\SurveyResultsQuery;
use SIIM\Application\Citizen\ReadModels\SurveyFilterOptions;
use SIIM\Application\Citizen\ReadModels\SurveyFilters;
use SIIM\Application\Citizen\UseCases\RevealSurveyContactUseCase;
use SIIM\Domain\Citizen\Channel;
use SIIM\Domain\Citizen\SurveyStatus;
use Tests\TestCase;

beforeEach(function (): void {
    /** @var TestCase $this */
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(SurveySeeder::class);
});

function panelUser(string $role): User
{
    $user = User::factory()->create(['email_verified_at' => now()]);
    $user->assignRole($role);

    return $user;
}

/**
 * @param  array<int, int|string|list<string>>  $answers
 */
function panelSurveyResponse(
    Survey $survey,
    array $answers,
    string $submittedAt,
    string $zone,
    string $age,
    ?string $commentText = null,
    ?string $polarity = null,
    ?string $contact = null,
): SurveyResponse {
    $comment = null;
    if ($commentText !== null) {
        $comment = Comment::factory()->create([
            'text' => $commentText,
            'channel' => Channel::WebSurvey,
            'source' => "survey:{$survey->slug}",
            'captured_at' => $submittedAt,
        ]);
        if ($polarity !== null) {
            SentimentScore::factory()->create(['comment_id' => $comment->id, 'polarity' => $polarity]);
        }
    }

    $ciphertext = $contact === null ? null : app(ContactEncryptor::class)->encrypt($contact);
    $response = SurveyResponse::factory()->for($survey)->create([
        'comment_id' => $comment?->id,
        'ip_hash' => hash('sha256', Str::uuid()->toString()),
        'submitted_at' => $submittedAt,
        'response_date' => substr($submittedAt, 0, 10),
        'zone' => $zone,
        'age_range' => $age,
        'respondent_contact' => $ciphertext,
    ]);
    $questions = $survey->questions->keyBy('position');
    foreach ($answers as $position => $value) {
        /** @var SurveyQuestion $question */
        $question = $questions->get($position);
        SurveyAnswer::query()->create([
            'response_id' => $response->id,
            'question_id' => $question->id,
            'value_int' => is_int($value) ? $value : null,
            'value_text' => is_string($value) ? $value : null,
            'value_json' => is_array($value) ? $value : null,
        ]);
    }

    return $response;
}

/** @return array{Survey, list<SurveyResponse>} */
function panelSurveyDataset(): array
{
    $survey = Survey::query()->where('slug', 'percepcion-2026')->with('questions')->firstOrFail();
    $rows = [
        panelSurveyResponse($survey, [3 => 5, 4 => 5, 9 => 10, 10 => ['seguridad', 'salud'], 11 => '=SUM(1,1)'], '2026-08-01 08:00:00', 'Centro', '18-25', '=SUM(1,1)', 'positive', 'vecino@example.test'),
        panelSurveyResponse($survey, [3 => 4, 4 => 4, 9 => 9, 10 => ['seguridad'], 11 => 'Atención rápida'], '2026-08-02 12:00:00', 'Norte', '26-35', 'Atención rápida', 'positive'),
        panelSurveyResponse($survey, [3 => 2, 4 => 2, 9 => 6, 10 => ['salud'], 11 => 'Falta iluminación'], '2026-08-03 23:59:59', 'Centro', '36-50', 'Falta iluminación', 'negative'),
        panelSurveyResponse($survey, [3 => 1, 4 => 1, 9 => 7, 10 => ['obras_publicas'], 11 => 'Pendiente de análisis'], '2026-08-04 09:00:00', 'Sur', '18-25', 'Pendiente de análisis'),
    ];

    return [$survey, $rows];
}

it('protects survey panel routes with the specified role matrix', function (): void {
    /** @var TestCase $this */
    $survey = Survey::query()->where('slug', 'percepcion-2026')->firstOrFail();

    $urls = ['/panel/encuestas', "/panel/encuestas/{$survey->slug}", "/panel/encuestas/{$survey->slug}/export"];
    foreach ($urls as $url) {
        $this->get($url)->assertRedirect('/login');
    }
    foreach ($urls as $url) {
        $this->actingAs(panelUser('citizen'))->get($url)->assertForbidden();
    }
    foreach ($urls as $url) {
        $this->actingAs(panelUser('analyst'))->get($url)->assertOk();
    }

    $this->get('/panel/encuestas/nueva')->assertForbidden();
    $this->actingAs(panelUser('admin'))->get('/panel/encuestas/nueva')->assertOk();
});

it('declares static survey routes before the dynamic survey result route', function (): void {
    /** @var TestCase $this */
    $admin = panelUser('admin');

    $this->actingAs($admin)->get('/panel/encuestas/nueva')->assertOk();
    $this->actingAs($admin)->get('/panel/encuestas/no-existe')->assertNotFound();

    expect(route('panel.surveys'))->toEndWith('/panel/encuestas')
        ->and(route('panel.surveys.create'))->toEndWith('/panel/encuestas/nueva')
        ->and(route('panel.surveys.results', ['slug' => 'percepcion-2026']))->toEndWith('/panel/encuestas/percepcion-2026')
        ->and(route('panel.surveys.export', ['slug' => 'percepcion-2026']))->toEndWith('/panel/encuestas/percepcion-2026/export');
});

it('starts every survey built in the panel as a draft', function (): void {
    /** @var TestCase $this */
    $admin = panelUser('admin');

    $this->actingAs($admin)->post('/panel/encuestas', [
        'title' => 'Encuesta de servicios del barrio',
        'slug' => 'servicios-barrio',
        'description' => 'Consulta breve.',
        'questions' => [[
            'label' => 'Califique el servicio',
            'type' => 'scale_1_5',
            'is_required' => '1',
        ]],
    ])->assertRedirect('/panel/encuestas/servicios-barrio');

    $survey = Survey::query()->where('slug', 'servicios-barrio')->with('questions')->firstOrFail();

    expect($survey->status)->toBe(SurveyStatus::Draft)
        ->and($survey->questions)->toHaveCount(1)
        ->and($survey->questions->sole()->position)->toBe(1);
});

it('accepts numeric builder inputs as browser submitted strings', function (): void {
    /** @var TestCase $this */
    $admin = panelUser('admin');

    $this->actingAs($admin)->post('/panel/encuestas', [
        'title' => 'Encuesta con preguntas configurables',
        'slug' => 'preguntas-configurables',
        'questions' => [
            [
                'label' => 'Elige prioridades',
                'type' => 'multi_choice',
                'is_required' => '1',
                'options' => "Seguridad\nSalud\nTransporte",
                'max_selections' => '2',
            ],
            [
                'label' => 'Cuéntanos más',
                'type' => 'open_text',
                'is_required' => '0',
                'max_length' => '250',
            ],
        ],
    ])->assertRedirect('/panel/encuestas/preguntas-configurables');

    $survey = Survey::query()->where('slug', 'preguntas-configurables')->with('questions')->firstOrFail();
    $firstQuestion = $survey->questions->get(0);
    $secondQuestion = $survey->questions->get(1);
    assert($firstQuestion instanceof SurveyQuestion);
    assert($secondQuestion instanceof SurveyQuestion);

    expect($survey->questions)->toHaveCount(2)
        ->and($firstQuestion->max_selections)->toBe(2)
        ->and($secondQuestion->max_length)->toBe(250);
});

it('calculates real KPIs distributions priorities and the exact shared filters', function (): void {
    [$survey] = panelSurveyDataset();
    $query = app(SurveyResultsQuery::class);
    $results = $query->results($survey->slug, new SurveyFilters);

    expect($results)->not->toBeNull()
        ->and($results?->totalResponses)->toBe(4)
        ->and($results?->generalAverage)->toBe(3.0)
        ->and($results?->nps)->toBe(25.0)
        ->and($results?->positiveSentimentPercent)->toBe(66.67)
        ->and($results?->scales[0]->buckets)->toBe([1 => 1, 2 => 1, 3 => 0, 4 => 1, 5 => 1])
        ->and($results?->priorities[0]->value)->toBe('seguridad')
        ->and($results?->priorities[0]->count)->toBe(2)
        ->and($results?->priorities[0]->percent)->toBe(50.0);

    $filtered = $query->results($survey->slug, new SurveyFilters(
        from: new DateTimeImmutable('2026-08-03'),
        to: new DateTimeImmutable('2026-08-03'),
        zone: 'Centro',
        ageRange: '36-50',
    ));
    expect($filtered?->totalResponses)->toBe(1)
        ->and($filtered?->generalAverage)->toBe(2.0)
        ->and($filtered?->comments->items)->toHaveCount(1)
        ->and($filtered?->comments->items[0]->text)->toBe('Falta iluminación');
});

it('normalizes numeric pagination query parameters submitted as strings', function (): void {
    $survey = Survey::query()->where('slug', 'percepcion-2026')->firstOrFail();
    $options = app(SurveyResultsQuery::class)->filterOptions($survey->slug);
    expect($options)->not->toBeNull();
    assert($options instanceof SurveyFilterOptions);

    $filters = app(SurveyFilterFactory::class)->fromRequest(
        Request::create('/panel/encuestas/' . $survey->slug, 'GET', ['page' => '2']),
        $options,
    );

    expect($filters->page)->toBe(2);
});

it('rejects unknown survey filters and inverted date ranges in Spanish', function (): void {
    /** @var TestCase $this */
    $survey = Survey::query()->where('slug', 'percepcion-2026')->firstOrFail();
    $this->actingAs(panelUser('analyst'))->getJson(route('panel.surveys.results', [
        'slug' => $survey->slug,
        'zone' => 'Zona inventada',
        'from' => '2026-08-04',
        'to' => '2026-08-03',
    ]))->assertUnprocessable()
        ->assertJsonValidationErrors(['zone', 'to'])
        ->assertJsonPath('errors.zone.0', 'La zona seleccionada no pertenece a esta encuesta.')
        ->assertJsonPath('errors.to.0', 'La fecha final no puede ser anterior a la inicial.');
});

it('derives completion rate from valid anonymous attempts and returns N/D without attempts', function (): void {
    [$survey, $responses] = panelSurveyDataset();
    foreach (array_slice($responses, 0, 2) as $response) {
        SurveyAttempt::query()->create([
            'id' => (string) Str::uuid(),
            'survey_id' => $survey->id,
            'response_id' => $response->id,
            'started_at' => now()->subMinute(),
            'completed_at' => now(),
        ]);
    }
    SurveyAttempt::query()->create([
        'id' => (string) Str::uuid(),
        'survey_id' => $survey->id,
        'started_at' => now(),
    ]);
    Survey::factory()->create(['title' => 'Sin intentos']);

    $items = collect(app(SurveyResultsQuery::class)->surveys())->keyBy('title');
    expect($items->get($survey->title)?->completionRate)->toBe(66.67)
        ->and($items->get('Sin intentos')?->completionRate)->toBeNull();
});

it('paginates comments in stable database order and searches escaped literal text', function (): void {
    [$survey] = panelSurveyDataset();
    $query = app(SurveyResultsQuery::class);

    $page = $query->results($survey->slug, new SurveyFilters(page: 1, perPage: 2));
    expect($page?->comments->total)->toBe(4)
        ->and($page?->comments->lastPage)->toBe(2)
        ->and($page?->comments->items[0]->text)->toBe('Pendiente de análisis')
        ->and($page?->comments->items[1]->text)->toBe('Falta iluminación');

    $search = $query->results($survey->slug, new SurveyFilters(search: 'SUM(1,1)', page: 9, perPage: 2));
    expect($search?->comments->total)->toBe(1)
        ->and($search?->comments->currentPage)->toBe(1)
        ->and($search?->comments->items[0]->text)->toBe('=SUM(1,1)');

    panelSurveyResponse(
        $survey,
        [3 => 3, 9 => 8, 11 => 'Cobertura literal 100%_confirmada'],
        '2026-08-05 10:00:00',
        'Norte',
        '18-25',
        'Cobertura literal 100%_confirmada',
    );
    $literal = $query->results($survey->slug, new SurveyFilters(search: '100%_'));
    expect($literal?->comments->total)->toBe(1)
        ->and($literal?->comments->items[0]->text)->toBe('Cobertura literal 100%_confirmada');
});

it('streams filtered Unicode CSV safely without contact data or the contact question', function (): void {
    /** @var TestCase $this */
    [$survey] = panelSurveyDataset();
    $analyst = panelUser('analyst');

    $response = $this->actingAs($analyst)->get(route('panel.surveys.export', [
        'slug' => $survey->slug,
        'zone' => 'Centro',
    ]));
    $response->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
    $content = $response->streamedContent();

    expect($content)->toStartWith("\xEF\xBB\xBF")
        ->and($content)->toContain("'=SUM(1,1)")
        ->and($content)->toContain('Falta iluminación')
        ->and($content)->toContain('contact_provided')
        ->and($content)->not->toContain('vecino@example.test')
        ->and($content)->not->toContain('Si deseas respuesta')
        ->and(substr_count($content, "\n"))->toBe(3);
});

it('neutralizes CSV formulas hidden behind leading whitespace and control characters', function (): void {
    /** @var TestCase $this */
    [$survey] = panelSurveyDataset();
    panelSurveyResponse(
        $survey,
        [3 => 3, 9 => 8, 11 => " \t=SUM(2,2)"],
        '2026-08-05 10:00:00',
        'Centro',
        '18-25',
        " \t=SUM(2,2)",
    );
    panelSurveyResponse(
        $survey,
        [3 => 3, 9 => 8, 11 => "\r@SUM(3,3)"],
        '2026-08-06 10:00:00',
        'Centro',
        '18-25',
        "\r@SUM(3,3)",
    );

    $content = $this->actingAs(panelUser('analyst'))->get(route('panel.surveys.export', [
        'slug' => $survey->slug,
        'zone' => 'Centro',
    ]))->streamedContent();

    expect($content)
        ->toContain("' \t=SUM(2,2)")
        ->toContain("'\r@SUM(3,3)");
});

it('reveals contact to admins only after an audit and prevents foreign-survey IDOR', function (): void {
    /** @var TestCase $this */
    [$survey, $responses] = panelSurveyDataset();
    $contactResponse = $responses[0];
    $analyst = panelUser('analyst');
    $admin = panelUser('admin');

    $this->actingAs($analyst)->postJson(route('panel.surveys.contact', ['slug' => $survey->slug, 'response' => $contactResponse->id]))->assertForbidden();
    $this->actingAs($analyst)->get(route('panel.surveys.results', ['slug' => $survey->slug]))
        ->assertOk()->assertDontSee('Revelar contacto');
    $revealed = $this->actingAs($admin)->postJson(route('panel.surveys.contact', ['slug' => $survey->slug, 'response' => $contactResponse->id]));
    $revealed->assertOk()->assertJson(['contact' => 'vecino@example.test']);
    expect((string) $revealed->headers->get('cache-control'))->toContain('private')->toContain('no-store')->toContain('max-age=0');

    expect(DB::table('survey_contact_access_logs')->count())->toBe(1)
        ->and(DB::table('survey_contact_access_logs')->value('request_hash'))->toBeString();

    $other = Survey::factory()->create(['slug' => 'otra-encuesta']);
    $this->actingAs($admin)->postJson(route('panel.surveys.contact', ['slug' => $other->slug, 'response' => $contactResponse->id]))->assertNotFound();
    expect(DB::table('survey_contact_access_logs')->count())->toBe(1);
});

it('never decrypts when persisting the contact audit fails', function (): void {
    /** @var TestCase $this */
    [$survey, $responses] = panelSurveyDataset();
    $decryptor = new class implements ContactEncryptor
    {
        public int $decryptions = 0;

        public function encrypt(string $plaintext): string
        {
            return $plaintext;
        }

        public function decrypt(string $ciphertext): string
        {
            $this->decryptions++;

            return $ciphertext;
        }

        public function cipher(): string
        {
            return 'test';
        }
    };
    app()->instance(ContactEncryptor::class, $decryptor);
    app()->instance(SurveyContactAccessRepository::class, new class implements SurveyContactAccessRepository
    {
        public function record(string $surveyId, string $responseId, int $userId, DateTimeImmutable $accessedAt, ?string $requestHash): void
        {
            throw new RuntimeException('audit unavailable');
        }
    });

    expect(fn () => app(RevealSurveyContactUseCase::class)->handle($survey->slug, $responses[0]->id, 1, new DateTimeImmutable, null))
        ->toThrow(RuntimeException::class, 'audit unavailable')
        ->and($decryptor->decryptions)->toBe(0);

    $this->actingAs(panelUser('admin'))
        ->postJson(route('panel.surveys.contact', ['slug' => $survey->slug, 'response' => $responses[0]->id]))
        ->assertStatus(503)
        ->assertJson(['message' => 'No pudimos revelar el contacto. Inténtalo nuevamente.'])
        ->assertDontSee('audit unavailable');
    expect($decryptor->decryptions)->toBe(0);
});

it('validates builder invariants atomically and leaves no partial survey', function (): void {
    /** @var TestCase $this */
    $admin = panelUser('admin');
    $before = Survey::query()->count();

    $this->actingAs($admin)->from('/panel/encuestas/nueva')->post('/panel/encuestas', [
        'title' => 'Encuesta inválida',
        'slug' => 'encuesta-invalida',
        'questions' => [[
            'label' => 'Elige una opción',
            'type' => 'multi_choice',
            'is_required' => '1',
            'options' => '',
            'max_selections' => 3,
        ]],
    ])->assertRedirect('/panel/encuestas/nueva')->assertSessionHasErrors('survey');

    expect(Survey::query()->count())->toBe($before);
});

it('shows the real active survey card only to reporting roles', function (): void {
    /** @var TestCase $this */
    $survey = Survey::query()->where('slug', 'percepcion-2026')->firstOrFail();
    SurveyResponse::factory()->for($survey)->create();

    $this->actingAs(panelUser('analyst'))->get('/panel')
        ->assertOk()->assertSee('Encuesta activa')->assertSee($survey->title)->assertSee('1 respuesta');
    $this->actingAs(panelUser('citizen'))->get('/panel')
        ->assertOk()->assertDontSee('Encuesta activa')->assertDontSee($survey->title);

    $survey->update(['status' => SurveyStatus::Closed]);
    $this->actingAs(panelUser('analyst'))->get('/panel')
        ->assertOk()->assertSee('Sin encuesta activa')->assertDontSee($survey->title);
});

it('keeps persistence and survey datasets outside new HTTP and view adapters', function (): void {
    $files = [
        ...glob(app_path('Http/Controllers/Survey*.php')) ?: [],
        app_path('Http/Controllers/RevealSurveyContactController.php'),
        ...glob(resource_path('views/panel/surveys/*.blade.php')) ?: [],
    ];
    foreach ($files as $file) {
        $contents = file_get_contents($file);
        assert(is_string($contents));
        foreach (['DB::', 'App\\Models', '->query()', 'percepcion-2026'] as $forbidden) {
            expect(str_contains($contents, $forbidden))->toBeFalse();
        }
    }
});
