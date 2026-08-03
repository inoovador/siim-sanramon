<?php

declare(strict_types=1);

namespace SIIM\Application\Analysis\UseCases;

use InvalidArgumentException;
use SIIM\Application\Analysis\Contracts\AnalysisClock;
use SIIM\Application\Analysis\Contracts\AnalysisRepository;
use SIIM\Application\Analysis\Contracts\FallbackSentimentAnalyzer;
use SIIM\Application\Analysis\Contracts\PrimarySentimentAnalyzer;
use SIIM\Application\Analysis\Data\AnalysisPersistence;
use SIIM\Application\Analysis\Exceptions\SentimentAnalysisException;

final readonly class AnalyzeCommentUseCase
{
    public function __construct(private AnalysisRepository $repository, private PrimarySentimentAnalyzer $primary, private FallbackSentimentAnalyzer $fallback, private AnalysisClock $clock, private bool $primaryConfigured, private float $dailyBudget) {}

    public function handle(string $commentId): void
    {
        $comment = $this->repository->findComment($commentId);
        if ($comment === null) {
            throw new InvalidArgumentException('Comment was not found.');
        }
        $requestedAt = $this->clock->now();
        $status = 'fallback';
        $error = null;
        if ($this->primaryConfigured && $this->repository->todayCost($requestedAt) < $this->dailyBudget) {
            try {
                $sentiment = $this->primary->analyze($comment->text);
                $status = 'success';
            } catch (SentimentAnalysisException) {
                $sentiment = $this->fallback->analyze($comment->text);
                $error = 'NVIDIA sentiment analysis was unavailable.';
            }
        } else {
            $sentiment = $this->fallback->analyze($comment->text);
            $error = $this->primaryConfigured ? 'Daily LLM budget reached.' : 'NVIDIA sentiment analysis is not configured.';
        }
        $this->repository->persist(new AnalysisPersistence($comment, $sentiment, $status, $error, $requestedAt, $this->clock->now()));
    }
}
