<?php

declare(strict_types=1);

use App\Jobs\AnalyzeCommentJob;
use App\Models\AnalysisRun;
use App\Models\Comment;
use App\Models\SentimentScore;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use SIIM\Application\Analysis\Contracts\AnalysisClock;
use SIIM\Application\Analysis\Contracts\AnalysisRepository;
use SIIM\Application\Analysis\Contracts\FallbackSentimentAnalyzer;
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
        ->and(AnalysisRun::query()->where('comment_id', $comment->id)->value('llm_provider'))->toBe('lexicon');
});

it('uses valid NVIDIA output under budget and the listener dispatches an after-commit analysis job', function (): void {
    Http::preventStrayRequests();
    Http::fake(['https://example.test/v1/chat/completions' => Http::response(['model' => 'sentiment-model', 'choices' => [['message' => ['content' => '{"polarity":"negative","score":-0.7,"confidence":0.8,"reason":"Crítica clara"}']]], 'usage' => ['prompt_tokens' => 9, 'completion_tokens' => 4]])]);
    config(['llm.providers.nvidia_glm.api_key' => 'test-key', 'llm.providers.nvidia_glm.base_url' => 'https://example.test/v1', 'llm.providers.nvidia_glm.model' => 'sentiment-model', 'llm.budgets.daily_usd' => 5.0]);
    $comment = Comment::factory()->create(['text' => 'El servicio es malo']);

    app(AnalyzeCommentUseCase::class)->handle((string) $comment->id);
    expect(AnalysisRun::query()->where('comment_id', $comment->id)->value('status'))->toBe('success');

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

    expect(AnalysisRun::query()->where('comment_id', $comment->id)->value('status'))->toBe('fallback');
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

it('persists lexical fallback when the primary retry budget is exhausted', function (): void {
    Http::preventStrayRequests();
    Http::fake([
        'https://budget-fallback.example.test/v1/chat/completions' => Http::sequence()->push([], 503)->push([], 503),
    ]);
    $delays = [];
    $primary = new NvidiaSentimentAnalyzer(
        'test-key',
        'https://budget-fallback.example.test/v1',
        'sentiment-model',
        sleeper: function (int $seconds) use (&$delays): void {
            $delays[] = $seconds;
        },
    );
    $comment = Comment::factory()->create(['text' => 'La atención fue mala']);

    (new AnalyzeCommentUseCase(
        app(AnalysisRepository::class),
        $primary,
        app(FallbackSentimentAnalyzer::class),
        app(AnalysisClock::class),
        true,
        5.0,
    ))->handle((string) $comment->id);

    expect($delays)->toBe([10])
        ->and(SentimentScore::query()->where('comment_id', $comment->id)->value('polarity'))->toBe('negative')
        ->and(AnalysisRun::query()->where('comment_id', $comment->id)->value('status'))->toBe('fallback');
    Http::assertSentCount(2);
});
