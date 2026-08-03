<?php

declare(strict_types=1);

namespace SIIM\Application\Analysis\Data;

use DateTimeImmutable;

final readonly class AnalysisPersistence
{
    public function __construct(
        public AnalyzableComment $comment,
        public SentimentAnalysis $sentiment,
        public string $status,
        public ?string $errorMessage,
        public DateTimeImmutable $requestedAt,
        public DateTimeImmutable $completedAt,
    ) {}
}
