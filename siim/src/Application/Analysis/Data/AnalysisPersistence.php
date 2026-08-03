<?php

declare(strict_types=1);

namespace SIIM\Application\Analysis\Data;

use DateTimeImmutable;

final readonly class AnalysisPersistence
{
    public function __construct(
        public AnalyzableComment $comment,
        public SentimentAnalysis $score,
        public bool $updateScore,
        public string $provider,
        public string $model,
        public string $status,
        public bool $fallbackUsed,
        public ?AnalysisErrorCategory $errorCategory,
        public ?string $errorMessage,
        public DateTimeImmutable $requestedAt,
        public DateTimeImmutable $completedAt,
        public TokenUsage $usage = new TokenUsage,
        public float $costUsd = 0.0,
    ) {}
}
