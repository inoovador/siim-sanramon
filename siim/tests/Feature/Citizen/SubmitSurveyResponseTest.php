<?php

declare(strict_types=1);

use App\Models\AnalysisRun;
use App\Models\Comment;
use App\Models\SentimentScore;
use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use SIIM\Application\Citizen\Commands\SubmitSurveyResponseCommand;
use SIIM\Application\Citizen\Contracts\CitizenSubmissionRepository;
use SIIM\Application\Citizen\Contracts\ContactEncryptor;
use SIIM\Application\Citizen\Data\SurveySubmission;
use SIIM\Application\Citizen\UseCases\SubmitSurveyResponseUseCase;
use SIIM\Domain\Citizen\Channel;
use SIIM\Domain\Citizen\Events\CommentIngested;
use SIIM\Domain\Citizen\Exceptions\DuplicateResponseException;
use SIIM\Domain\Citizen\Exceptions\SurveyClosedException;
use SIIM\Domain\Citizen\QuestionType;
use SIIM\Domain\Citizen\SurveyStatus;
use SIIM\Infrastructure\Persistence\Citizen\EloquentCitizenSubmissionRepository;

function submissionSurvey(SurveyStatus $status = SurveyStatus::Published): Survey
{
    static $number = 0;
    $number++;
    $survey = Survey::factory()->create(['slug' => "percepcion-2026-{$number}", 'status' => $status]);
    $types = [QuestionType::SingleChoice, QuestionType::SingleChoice, QuestionType::Scale1To5, QuestionType::Scale1To5, QuestionType::Scale1To5, QuestionType::Scale1To5, QuestionType::Scale1To5, QuestionType::Scale1To5, QuestionType::Nps, QuestionType::MultiChoice, QuestionType::OpenText, QuestionType::OpenText];

    foreach ($types as $position => $type) {
        SurveyQuestion::factory()->create([
            'survey_id' => $survey->id,
            'position' => $position + 1,
            'type' => $type,
            'is_required' => $position < 10,
            'options' => match ($position + 1) {
                1 => ['Centro', 'Norte'],
                2 => ['18-25', '26-35'],
                10 => ['seguridad', 'salud', 'obras'],
                default => null,
            },
            'max_selections' => $position === 9 ? 3 : null,
            'max_length' => $position === 10 ? 1000 : ($position === 11 ? 120 : null),
        ]);
    }

    $fresh = $survey->fresh('questions');
    assert($fresh instanceof Survey);

    return $fresh;
}

function submissionQuestion(Survey $survey, int $position): SurveyQuestion
{
    $question = $survey->questions->firstWhere('position', $position);
    assert($question instanceof SurveyQuestion);

    return $question;
}

function submissionCommand(Survey $survey, ?string $comment = 'Excelente atención', ?string $contact = 'ana@example.test', string $ip = '203.0.113.9', string $submittedAt = '2026-08-02 10:00:00'): SubmitSurveyResponseCommand
{
    /** @var array<string, mixed> $answers */
    $answers = [
        (string) submissionQuestion($survey, 1)->id => 'Centro', (string) submissionQuestion($survey, 2)->id => '18-25', (string) submissionQuestion($survey, 3)->id => 5,
        (string) submissionQuestion($survey, 4)->id => 4, (string) submissionQuestion($survey, 5)->id => 3, (string) submissionQuestion($survey, 6)->id => 4,
        (string) submissionQuestion($survey, 7)->id => 5, (string) submissionQuestion($survey, 8)->id => 4, (string) submissionQuestion($survey, 9)->id => 9,
        (string) submissionQuestion($survey, 10)->id => ['seguridad', 'salud'], (string) submissionQuestion($survey, 11)->id => $comment,
        (string) submissionQuestion($survey, 12)->id => $contact,
    ];

    return new SubmitSurveyResponseCommand(
        surveySlug: $survey->slug,
        answersByQuestionId: $answers,
        ipAddress: $ip,
        userAgent: 'Task 2 test agent',
        completionMs: 1234,
        submittedAt: new DateTimeImmutable($submittedAt),
    );
}

it('stores an encrypted transactional response and publishes its comment after success', function (): void {
    config(['app.cipher' => 'AES-256-GCM']);
    $survey = submissionSurvey();
    Event::fake([CommentIngested::class]);

    $result = app(SubmitSurveyResponseUseCase::class)->handle(submissionCommand($survey));
    $response = SurveyResponse::query()->findOrFail($result->responseId);
    $comment = Comment::query()->findOrFail($response->comment_id);
    assert(is_string($response->respondent_contact));
    $appKey = config('app.key');
    assert(is_string($appKey));

    expect(SurveyResponse::query()->count())->toBe(1)
        ->and(Comment::query()->count())->toBe(1)
        ->and(SurveyAnswer::query()->where('response_id', $response->id)->count())->toBe(12)
        ->and($response->zone)->toBe('Centro')
        ->and($response->age_range)->toBe('18-25')
        ->and($response->ip_hash)->toBe(hash('sha256', '203.0.113.9' . config('app.key')))
        ->and($response->user_agent_hash)->toBe(hash_hmac('sha256', 'Task 2 test agent', $appKey))
        ->and($response->getRawOriginal('response_date'))->toBe('2026-08-02')
        ->and($response->respondent_contact)->not->toContain('ana@example.test')
        ->and(Crypt::decryptString($response->respondent_contact))->toBe('ana@example.test')
        ->and(app(ContactEncryptor::class)->cipher())->toBe('AES-256-GCM')
        ->and(SurveyAnswer::query()->where('response_id', $response->id)->where('question_id', submissionQuestion($survey, 12)->id)->value('value_text'))->toBe($response->respondent_contact)
        ->and($comment->channel)->toBe(Channel::WebSurvey)
        ->and($comment->source)->toBe("survey:{$survey->slug}:{$response->id}")
        ->and($comment->language)->toBe('es')
        ->and($comment->getRawOriginal('captured_at'))->toBe('2026-08-02 10:00:00');
    expect(json_encode($response->getAttributes(), JSON_THROW_ON_ERROR))->not->toContain('203.0.113.9');

    Event::assertDispatchedTimes(CommentIngested::class, 1);
    Event::assertDispatched(CommentIngested::class, fn (CommentIngested $event): bool => $event->commentId === $response->comment_id);
});

it('accepts blank comments without emitting an analysis event', function (): void {
    $survey = submissionSurvey();
    Event::fake([CommentIngested::class]);

    app(SubmitSurveyResponseUseCase::class)->handle(submissionCommand($survey, '   '));

    expect(Comment::query()->count())->toBe(0);
    Event::assertNotDispatched(CommentIngested::class);
});

it('rejects closed surveys and duplicate daily fingerprints without persistence', function (): void {
    $closed = submissionSurvey(SurveyStatus::Draft);
    expect(fn () => app(SubmitSurveyResponseUseCase::class)->handle(submissionCommand($closed)))->toThrow(SurveyClosedException::class);
    expect(SurveyResponse::query()->count())->toBe(0);

    $survey = submissionSurvey();
    app(SubmitSurveyResponseUseCase::class)->handle(submissionCommand($survey));
    expect(fn () => app(SubmitSurveyResponseUseCase::class)->handle(submissionCommand($survey)))->toThrow(DuplicateResponseException::class);
});

it('rejects draft, closed, and out-of-window surveys without persistence', function (): void {
    $draft = submissionSurvey(SurveyStatus::Draft);
    $closed = submissionSurvey(SurveyStatus::Closed);
    $future = submissionSurvey();
    $future->update(['opens_at' => '2026-08-03 00:00:00']);
    $future = $future->fresh('questions');
    assert($future instanceof Survey);

    foreach ([$draft, $closed, $future] as $survey) {
        expect(fn () => app(SubmitSurveyResponseUseCase::class)->handle(submissionCommand($survey)))->toThrow(SurveyClosedException::class);
    }

    expect(SurveyResponse::query()->count())->toBe(0)->and(SurveyAnswer::query()->count())->toBe(0)->and(Comment::query()->count())->toBe(0);
});

it('translates only the named MariaDB dedupe constraint as the concurrency backstop', function (): void {
    $survey = submissionSurvey();
    app(SubmitSurveyResponseUseCase::class)->handle(submissionCommand($survey));
    $delegate = app(EloquentCitizenSubmissionRepository::class);
    app()->bind(CitizenSubmissionRepository::class, fn (): CitizenSubmissionRepository => new class($delegate) implements CitizenSubmissionRepository
    {
        public function __construct(private EloquentCitizenSubmissionRepository $delegate) {}

        public function existsForDate(string $surveyId, string $ipHash, DateTimeImmutable $date): bool
        {
            return false;
        }

        public function save(SurveySubmission $submission): void
        {
            $this->delegate->save($submission);
        }
    });

    expect(fn () => app(SubmitSurveyResponseUseCase::class)->handle(submissionCommand($survey)))->toThrow(DuplicateResponseException::class);
    expect(SurveyResponse::query()->count())->toBe(1)->and(Comment::query()->count())->toBe(1);
});

it('accepts an omitted P11 and permits the same fingerprint on another day or survey', function (): void {
    $survey = submissionSurvey();
    $omitted = submissionCommand($survey);
    $answers = $omitted->answersByQuestionId;
    unset($answers[(string) submissionQuestion($survey, 11)->id]);
    $withoutP11 = new SubmitSurveyResponseCommand($omitted->surveySlug, $answers, $omitted->ipAddress, $omitted->userAgent, $omitted->completionMs, $omitted->submittedAt);
    app(SubmitSurveyResponseUseCase::class)->handle($withoutP11);

    $nextDay = submissionCommand($survey, submittedAt: '2026-08-03 10:00:00');
    app(SubmitSurveyResponseUseCase::class)->handle($nextDay);
    $otherSurvey = submissionSurvey();
    app(SubmitSurveyResponseUseCase::class)->handle(submissionCommand($otherSurvey));

    expect(SurveyResponse::query()->count())->toBe(3)->and(Comment::query()->count())->toBe(2);
});

it('rolls back response, answers, and comment without publishing when answer persistence fails', function (): void {
    $survey = submissionSurvey();
    Event::fake([CommentIngested::class]);
    $createdAnswers = 0;
    SurveyAnswer::creating(static function () use (&$createdAnswers): void {
        $createdAnswers++;
        if ($createdAnswers > 1) {
            throw new RuntimeException('test persistence failure');
        }
    });

    try {
        expect(fn () => app(SubmitSurveyResponseUseCase::class)->handle(submissionCommand($survey)))->toThrow(RuntimeException::class, 'test persistence failure');
    } finally {
        SurveyAnswer::flushEventListeners();
    }

    expect($createdAnswers)->toBe(2)->and(SurveyResponse::query()->count())->toBe(0)->and(SurveyAnswer::query()->count())->toBe(0)->and(Comment::query()->count())->toBe(0);
    Event::assertNotDispatched(CommentIngested::class);
});

it('does not persist invalid answers or raw personal data', function (): void {
    $survey = submissionSurvey();
    $command = submissionCommand($survey);
    /** @var array<string, mixed> $answers */
    $answers = $command->answersByQuestionId;
    $answers[(string) submissionQuestion($survey, 3)->id] = 6;
    $invalid = new SubmitSurveyResponseCommand($command->surveySlug, $answers, '198.51.100.7', $command->userAgent, $command->completionMs, $command->submittedAt);

    expect(fn () => app(SubmitSurveyResponseUseCase::class)->handle($invalid))->toThrow(InvalidArgumentException::class, 'integer from 1 to 5');
    expect(SurveyResponse::query()->count())->toBe(0)->and(SurveyAnswer::query()->count())->toBe(0)->and(Comment::query()->count())->toBe(0);
});

it('keeps P12 plaintext out of every query-visible persisted field', function (): void {
    $survey = submissionSurvey();
    $email = 'private.person@example.test';
    $result = app(SubmitSurveyResponseUseCase::class)->handle(submissionCommand($survey, contact: $email));
    $response = SurveyResponse::query()->findOrFail($result->responseId);

    $persisted = json_encode([
        DB::table('survey_responses')->where('id', $response->id)->first(),
        DB::table('survey_answers')->where('response_id', $response->id)->get(),
        DB::table('comments')->where('id', $response->comment_id)->first(),
    ], JSON_THROW_ON_ERROR);

    expect($persisted)->not->toContain($email)
        ->and(app(ContactEncryptor::class)->decrypt((string) $response->respondent_contact))->toBe($email);
});

it('rejects missing, malformed, and over-selected answers without persistence', function (): void {
    $survey = submissionSurvey();
    $command = submissionCommand($survey);
    $cases = [
        function (array $answers) use ($survey): array {
            unset($answers[(string) submissionQuestion($survey, 1)->id]);

            return $answers;
        },
        function (array $answers) use ($survey): array {
            $answers[(string) submissionQuestion($survey, 3)->id] = 'five';

            return $answers;
        },
        function (array $answers) use ($survey): array {
            $answers[(string) submissionQuestion($survey, 10)->id] = ['seguridad', 'salud', 'obras', 'seguridad'];

            return $answers;
        },
    ];

    foreach ($cases as $mutate) {
        expect(fn () => app(SubmitSurveyResponseUseCase::class)->handle(new SubmitSurveyResponseCommand($command->surveySlug, $mutate($command->answersByQuestionId), $command->ipAddress, $command->userAgent, $command->completionMs, $command->submittedAt)))->toThrow(InvalidArgumentException::class);
    }

    expect(SurveyResponse::query()->count())->toBe(0)->and(SurveyAnswer::query()->count())->toBe(0)->and(Comment::query()->count())->toBe(0);
});

it('dispatches the internal listener/job flow to lexical fallback without network access', function (): void {
    Http::preventStrayRequests();
    config(['llm.providers.nvidia_glm.api_key' => null]);
    $survey = submissionSurvey();

    $result = app(SubmitSurveyResponseUseCase::class)->handle(submissionCommand($survey));

    expect(SentimentScore::query()->where('comment_id', $result->commentId)->count())->toBe(1)
        ->and(AnalysisRun::query()->where('comment_id', $result->commentId)->value('status'))->toBe('fallback');
});
