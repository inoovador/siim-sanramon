<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use SIIM\Application\Analysis\Exceptions\RetryableSentimentAnalysisException;
use SIIM\Application\Analysis\UseCases\AnalyzeCommentUseCase;
use Throwable;

final class AnalyzeCommentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 4;

    public int $timeout = 90;

    /** @var list<int> */
    public array $backoff = [10, 60, 300];

    public function __construct(public readonly string $commentId)
    {
        $this->onQueue('analysis')->afterCommit();
    }

    public function handle(AnalyzeCommentUseCase $useCase): void
    {
        $useCase->handle($this->commentId);
    }

    public function failed(?Throwable $exception): void
    {
        if ($exception instanceof RetryableSentimentAnalysisException) {
            app(AnalyzeCommentUseCase::class)->markFailed($this->commentId);
        }
    }
}
