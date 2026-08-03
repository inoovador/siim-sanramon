<?php

declare(strict_types=1);

namespace SIIM\Application\Analysis\UseCases;

use DateTimeImmutable;
use InvalidArgumentException;
use SIIM\Application\Analysis\Contracts\AnalysisClock;
use SIIM\Application\Analysis\Contracts\AnalysisRepository;
use SIIM\Application\Analysis\Contracts\FallbackSentimentAnalyzer;
use SIIM\Application\Analysis\Contracts\PrimarySentimentAnalyzer;
use SIIM\Application\Analysis\Data\AnalysisErrorCategory;
use SIIM\Application\Analysis\Data\AnalysisPersistence;
use SIIM\Application\Analysis\Data\AnalyzableComment;
use SIIM\Application\Analysis\Data\SentimentAnalysis;
use SIIM\Application\Analysis\Data\TokenUsage;
use SIIM\Application\Analysis\Exceptions\RetryableSentimentAnalysisException;
use SIIM\Application\Analysis\Exceptions\SentimentAnalysisException;
use SIIM\Application\Analysis\Exceptions\TerminalSentimentAnalysisException;

final readonly class AnalyzeCommentUseCase
{
    private const EXHAUSTED_RETRIES_MESSAGE = 'Provider retries were exhausted; lexical fallback was applied.';

    public function __construct(
        private AnalysisRepository $repository,
        private PrimarySentimentAnalyzer $primary,
        private FallbackSentimentAnalyzer $fallback,
        private AnalysisClock $clock,
        private bool $primaryConfigured,
        private float $dailyBudget,
    ) {}

    public function handle(string $commentId): void
    {
        $comment = $this->findComment($commentId);
        if ($this->repository->isComplete($commentId)) {
            return;
        }

        $requestedAt = $this->clock->now();
        $lexical = $this->fallback->analyze($comment->text);

        if (! $this->primaryConfigured) {
            $this->persist(
                $comment,
                $lexical,
                true,
                $this->fallback->provider(),
                $this->fallback->model(),
                'fallback',
                true,
                AnalysisErrorCategory::NotConfigured,
                'Primary sentiment analysis is not configured.',
                $requestedAt,
            );

            return;
        }

        if ($this->repository->todayCost($requestedAt) >= $this->dailyBudget) {
            $this->persist(
                $comment,
                $lexical,
                true,
                $this->fallback->provider(),
                $this->fallback->model(),
                'fallback',
                true,
                AnalysisErrorCategory::BudgetExhausted,
                'Daily provider budget was exhausted.',
                $requestedAt,
            );

            return;
        }

        try {
            $sentiment = $this->primary->analyze($comment->text);
        } catch (RetryableSentimentAnalysisException $exception) {
            $this->persistProviderFailure($comment, $lexical, $exception, $requestedAt, false);

            throw $exception;
        } catch (TerminalSentimentAnalysisException $exception) {
            $this->persistProviderFailure($comment, $lexical, $exception, $requestedAt, true);

            return;
        }

        $this->persist(
            $comment,
            $sentiment,
            true,
            $sentiment->provider,
            $sentiment->model,
            'success',
            false,
            null,
            null,
            $requestedAt,
            $sentiment->usage,
            $sentiment->costUsd,
        );
    }

    public function markFailed(string $commentId): void
    {
        $comment = $this->findComment($commentId);
        if ($this->repository->isComplete($commentId)) {
            return;
        }

        $requestedAt = $this->clock->now();
        $lexical = $this->fallback->analyze($comment->text);
        $this->persist(
            $comment,
            $lexical,
            true,
            $this->fallback->provider(),
            $this->fallback->model(),
            'fallback',
            true,
            AnalysisErrorCategory::RetriesExhausted,
            self::EXHAUSTED_RETRIES_MESSAGE,
            $requestedAt,
        );
    }

    private function findComment(string $commentId): AnalyzableComment
    {
        $comment = $this->repository->findComment($commentId);
        if ($comment === null) {
            throw new InvalidArgumentException('Comment was not found.');
        }

        return $comment;
    }

    private function persistProviderFailure(
        AnalyzableComment $comment,
        SentimentAnalysis $lexical,
        SentimentAnalysisException $exception,
        DateTimeImmutable $requestedAt,
        bool $terminal,
    ): void {
        $this->persist(
            $comment,
            $lexical,
            $terminal,
            $this->primary->provider(),
            $this->primary->model(),
            $terminal ? 'fallback' : 'failed',
            $terminal,
            $exception->category,
            $exception->getMessage(),
            $requestedAt,
        );
    }

    private function persist(
        AnalyzableComment $comment,
        SentimentAnalysis $score,
        bool $updateScore,
        string $provider,
        string $model,
        string $status,
        bool $fallbackUsed,
        ?AnalysisErrorCategory $errorCategory,
        ?string $errorMessage,
        DateTimeImmutable $requestedAt,
        TokenUsage $usage = new TokenUsage,
        float $costUsd = 0.0,
    ): void {
        $this->repository->persist(new AnalysisPersistence(
            $comment,
            $score,
            $updateScore,
            $provider,
            $model,
            $status,
            $fallbackUsed,
            $errorCategory,
            $errorMessage,
            $requestedAt,
            $this->clock->now(),
            $usage,
            $costUsd,
        ));
    }
}
