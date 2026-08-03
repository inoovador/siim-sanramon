<?php

declare(strict_types=1);

use App\Jobs\AnalyzeCommentJob;
use App\Models\AnalysisRun;
use App\Models\Comment;
use App\Models\SentimentScore;
use App\Models\TopicAssignment;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use SIIM\Application\Analysis\Contracts\AnalysisClock;
use SIIM\Application\Analysis\Contracts\AnalysisRepository;
use SIIM\Application\Analysis\Contracts\FallbackSentimentAnalyzer;
use SIIM\Application\Analysis\Exceptions\RetryableSentimentAnalysisException;
use SIIM\Application\Analysis\UseCases\AnalyzeCommentUseCase;
use SIIM\Domain\Citizen\Events\CommentIngested;
use SIIM\Infrastructure\Llm\NvidiaSentimentAnalyzer;

it('falls back offline and persists exactly one score and analysis run when NVIDIA is unavailable', function (): void {
    Http::preventStrayRequests();
    config(['llm.providers.nvidia_glm.api_key' => null, 'llm.budgets.daily_usd' => 5.0]);
    $comment = Comment::factory()->create(['text' => 'El servicio es excelente y rápido']);

    app(AnalyzeCommentUseCase::class)->handle((string) $comment->id);

    expect(SentimentScore::query()->whereKey($comment->id)->value('polarity'))->toBe('positive')
        ->and(AnalysisRun::query()->where('comment_id', $comment->id)->value('status'))->toBe('fallback')
        ->and(AnalysisRun::query()->where('comment_id', $comment->id)->value('llm_provider'))->toBe('lexicon')
        ->and(AnalysisRun::query()->where('comment_id', $comment->id)->value('fallback_used'))->toBeTrue()
        ->and(AnalysisRun::query()->where('comment_id', $comment->id)->value('error_category'))->toBe('not_configured');
});

it('uses valid NVIDIA output under budget and the listener dispatches an after-commit analysis job', function (): void {
    Http::preventStrayRequests();
    Http::fake(['https://example.test/v1/chat/completions' => Http::response(['model' => 'sentiment-model', 'choices' => [['message' => ['content' => '{"polarity":"negative","score":-0.7,"confidence":0.8,"reason":"Crítica clara"}']]], 'usage' => ['prompt_tokens' => 9, 'completion_tokens' => 4]])]);
    config(['llm.providers.nvidia_glm.api_key' => 'test-key', 'llm.providers.nvidia_glm.base_url' => 'https://example.test/v1', 'llm.providers.nvidia_glm.model' => 'sentiment-model', 'llm.budgets.daily_usd' => 5.0]);
    $comment = Comment::factory()->create(['text' => 'El servicio es malo']);

    app(AnalyzeCommentUseCase::class)->handle((string) $comment->id);
    expect(AnalysisRun::query()->where('comment_id', $comment->id)->value('status'))->toBe('success')
        ->and(AnalysisRun::query()->where('comment_id', $comment->id)->value('fallback_used'))->toBeFalse()
        ->and(AnalysisRun::query()->where('comment_id', $comment->id)->value('tokens_input'))->toBe(9)
        ->and(AnalysisRun::query()->where('comment_id', $comment->id)->value('tokens_output'))->toBe(4);

    Queue::fake();
    event(new CommentIngested((string) $comment->id));
    Queue::assertPushed(AnalyzeCommentJob::class, fn (AnalyzeCommentJob $job): bool => $job->commentId === $comment->id && $job->queue === 'analysis' && $job->afterCommit);
});

it('does not call NVIDIA after the daily budget has been reached', function (): void {
    Http::preventStrayRequests();
    config(['llm.providers.nvidia_glm.api_key' => 'test-key', 'llm.budgets.daily_usd' => 1.0]);
    AnalysisRun::factory()->create(['cost_usd' => 1.0, 'requested_at' => now()]);
    $comment = Comment::factory()->create(['text' => 'La atención fue buena']);

    app(AnalyzeCommentUseCase::class)->handle((string) $comment->id);

    expect(AnalysisRun::query()->where('comment_id', $comment->id)->value('status'))->toBe('fallback')
        ->and(AnalysisRun::query()->where('comment_id', $comment->id)->value('error_category'))->toBe('budget_exhausted');
    Http::assertNothingSent();
});

it('records requested and completed timestamps at their respective analysis boundaries', function (): void {
    config(['llm.providers.nvidia_glm.api_key' => null]);
    app()->bind(AnalysisClock::class, fn (): object => new class implements AnalysisClock
    {
        /** @var list<DateTimeImmutable> */
        private array $times;

        public function __construct()
        {
            $this->times = [new DateTimeImmutable('2026-08-02 09:00:00'), new DateTimeImmutable('2026-08-02 09:00:05')];
        }

        public function now(): DateTimeImmutable
        {
            return array_shift($this->times) ?? new DateTimeImmutable('2026-08-02 09:00:05');
        }
    });
    $comment = Comment::factory()->create(['text' => 'Atención correcta']);

    app(AnalyzeCommentUseCase::class)->handle((string) $comment->id);

    $run = AnalysisRun::query()->where('comment_id', $comment->id)->firstOrFail();
    expect($run->getRawOriginal('requested_at'))->toBe('2026-08-02 09:00:00')
        ->and($run->getRawOriginal('completed_at'))->toBe('2026-08-02 09:00:05');
});

it('appends a completed audit row for a retryable provider attempt and a separate terminal fallback', function (): void {
    Http::preventStrayRequests();
    Http::fake([
        'https://always-fails.example.test/v1/chat/completions' => Http::response(['private' => 'provider-secret-body'], 503),
    ]);
    $primary = new NvidiaSentimentAnalyzer('test-key', 'https://always-fails.example.test/v1', 'sentiment-model');
    $comment = Comment::factory()->create(['text' => 'La atención fue mala']);

    $useCase = new AnalyzeCommentUseCase(
        app(AnalysisRepository::class),
        $primary,
        app(FallbackSentimentAnalyzer::class),
        app(AnalysisClock::class),
        true,
        5.0,
    );
    $job = new AnalyzeCommentJob((string) $comment->id);
    $failure = null;

    try {
        $job->handle($useCase);
    } catch (RetryableSentimentAnalysisException $exception) {
        $failure = $exception;
    }

    $failedRun = AnalysisRun::query()->where('comment_id', $comment->id)->firstOrFail();
    expect($failure)->toBeInstanceOf(RetryableSentimentAnalysisException::class)
        ->and($failure?->getMessage())->not->toContain('provider-secret-body')
        ->and($failure?->getMessage())->not->toContain('test-key')
        ->and(SentimentScore::query()->where('comment_id', $comment->id)->count())->toBe(0)
        ->and(AnalysisRun::query()->where('comment_id', $comment->id)->count())->toBe(1)
        ->and($failedRun->llm_provider)->toBe('nvidia')
        ->and($failedRun->status)->toBe('failed')
        ->and($failedRun->fallback_used)->toBeFalse()
        ->and($failedRun->error_category)->toBe('provider_unavailable')
        ->and($failedRun->completed_at)->not->toBeNull();

    $job->failed($failure);
    $job->failed($failure);

    $terminalRun = AnalysisRun::query()
        ->where('comment_id', $comment->id)
        ->where('status', 'fallback')
        ->firstOrFail();
    expect($terminalRun->id)->not->toBe($failedRun->id)
        ->and($terminalRun->llm_provider)->toBe('lexicon')
        ->and($terminalRun->status)->toBe('fallback')
        ->and($terminalRun->fallback_used)->toBeTrue()
        ->and($terminalRun->error_category)->toBe('retries_exhausted')
        ->and($terminalRun->error_message)->toBeString()
        ->and(mb_strlen((string) $terminalRun->error_message))->toBeLessThanOrEqual(500)
        ->and($terminalRun->error_message)->not->toContain('provider-secret-body')
        ->and(SentimentScore::query()->where('comment_id', $comment->id)->count())->toBe(1)
        ->and(AnalysisRun::query()->where('comment_id', $comment->id)->where('status', 'fallback')->count())->toBe(1)
        ->and(AnalysisRun::query()->where('comment_id', $comment->id)->count())->toBe(2);
    Http::assertSentCount(1);
});

it('keeps failed attempts when a later queue attempt succeeds while updating only the score projection', function (): void {
    Http::preventStrayRequests();
    $requests = 0;
    Http::fake([
        'https://eventual-success.example.test/v1/chat/completions' => function () use (&$requests): mixed {
            $requests++;

            return $requests === 1
                ? Http::response(['private' => 'provider-secret-body'], 503)
                : Http::response([
                    'model' => 'sentiment-model',
                    'choices' => [['message' => ['content' => '{"polarity":"positive","score":0.7,"confidence":0.9,"reason":"Resultado primario"}']]],
                    'usage' => ['prompt_tokens' => 8, 'completion_tokens' => 4],
                ]);
        },
    ]);
    $primary = new NvidiaSentimentAnalyzer('test-key', 'https://eventual-success.example.test/v1', 'sentiment-model');
    $useCase = new AnalyzeCommentUseCase(
        app(AnalysisRepository::class),
        $primary,
        app(FallbackSentimentAnalyzer::class),
        app(AnalysisClock::class),
        true,
        5.0,
    );
    $comment = Comment::factory()->create(['text' => 'El servicio es malo']);
    $job = new AnalyzeCommentJob((string) $comment->id);

    expect(fn () => $job->handle($useCase))->toThrow(RetryableSentimentAnalysisException::class);
    expect(SentimentScore::query()->where('comment_id', $comment->id)->count())->toBe(0);
    $failedRun = AnalysisRun::query()->where('comment_id', $comment->id)->firstOrFail();
    expect($failedRun->llm_provider)->toBe('nvidia')->and($failedRun->status)->toBe('failed');
    $topicAssignment = TopicAssignment::factory()->create(['comment_id' => $comment->id]);

    $job->handle($useCase);
    $job->handle($useCase);

    $nvidiaScore = SentimentScore::query()->where('comment_id', $comment->id)->firstOrFail();
    $nvidiaRun = AnalysisRun::query()->where('comment_id', $comment->id)->where('status', 'success')->firstOrFail();
    expect($nvidiaScore->polarity)->toBe('positive')
        ->and($nvidiaRun->id)->not->toBe($failedRun->id)
        ->and($nvidiaRun->llm_provider)->toBe('nvidia')
        ->and($nvidiaRun->status)->toBe('success')
        ->and($nvidiaRun->error_message)->toBeNull()
        ->and(SentimentScore::query()->where('comment_id', $comment->id)->count())->toBe(1)
        ->and(AnalysisRun::query()->where('comment_id', $comment->id)->count())->toBe(2)
        ->and(AnalysisRun::query()->whereKey($failedRun->id)->value('status'))->toBe('failed')
        ->and(TopicAssignment::query()->where('comment_id', $comment->id)->count())->toBe(1)
        ->and(TopicAssignment::query()->where('comment_id', $comment->id)->value('topic_id'))->toBe($topicAssignment->topic_id);
    Http::assertSentCount(2);
});

it('declares queue-owned retry timing within the worker timeout', function (): void {
    $job = new AnalyzeCommentJob('00000000-0000-0000-0000-000000000001');

    expect($job->tries)->toBe(4)
        ->and($job->timeout)->toBe(90)
        ->and($job->backoff)->toBe([10, 60, 300])
        ->and(NvidiaSentimentAnalyzer::REQUEST_TIMEOUT_SECONDS)->toBeLessThan($job->timeout);
});

it('runs four retryable attempts through the database worker and then persists final fallback', function (): void {
    config([
        'queue.default' => 'database',
        'queue.connections.database.connection' => config('database.default'),
        'queue.failed.driver' => 'database-uuids',
        'llm.providers.nvidia_glm.api_key' => 'test-key',
        'llm.providers.nvidia_glm.base_url' => 'https://queue-retry.example.test/v1',
        'llm.providers.nvidia_glm.model' => 'sentiment-model',
        'llm.providers.nvidia_glm.verify_ssl' => true,
        'llm.budgets.daily_usd' => 5.0,
    ]);
    Http::preventStrayRequests();
    $requestOptions = [];
    Http::fake([
        'https://queue-retry.example.test/v1/chat/completions' => function (Request $request, array $options) use (&$requestOptions): mixed {
            $requestOptions[] = $options;

            return Http::response(['private' => 'provider-secret-body'], 503);
        },
    ]);
    $comment = Comment::factory()->create(['text' => 'El servicio es malo']);
    Queue::connection('database')->push(new AnalyzeCommentJob((string) $comment->id), '', 'analysis');

    foreach ([10, 60, 300] as $attemptIndex => $expectedBackoff) {
        $exitCode = Artisan::call('queue:work', [
            'connection' => 'database',
            '--queue' => 'analysis',
            '--once' => true,
            '--tries' => 4,
            '--timeout' => 90,
            '--sleep' => 0,
        ]);
        $queued = DB::table('jobs')->where('queue', 'analysis')->first();
        assert($queued instanceof stdClass);

        expect($exitCode)->toBe(0)
            ->and((int) $queued->attempts)->toBe($attemptIndex + 1)
            ->and((int) $queued->available_at)->toBeGreaterThanOrEqual(time() + $expectedBackoff - 2);

        DB::table('jobs')->where('id', $queued->id)->update(['available_at' => now()->subSecond()->timestamp]);
    }

    Artisan::call('queue:work', [
        'connection' => 'database',
        '--queue' => 'analysis',
        '--once' => true,
        '--tries' => 4,
        '--timeout' => 90,
        '--sleep' => 0,
    ]);

    expect(DB::table('jobs')->where('queue', 'analysis')->count())->toBe(0)
        ->and(DB::table('failed_jobs')->count())->toBe(1)
        ->and(AnalysisRun::query()->where('comment_id', $comment->id)->where('status', 'failed')->count())->toBe(4)
        ->and(AnalysisRun::query()->where('comment_id', $comment->id)->where('status', 'fallback')->count())->toBe(1)
        ->and(AnalysisRun::query()->where('comment_id', $comment->id)->count())->toBe(5)
        ->and(SentimentScore::query()->where('comment_id', $comment->id)->value('polarity'))->toBe('negative')
        ->and(collect($requestOptions)->max(fn (array $options): int => (int) ($options['timeout'] ?? 0)))->toBeLessThan(90);
    Http::assertSentCount(4);
});
