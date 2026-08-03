<?php

declare(strict_types=1);

namespace SIIM\Application\Analysis\Data;

final readonly class SentimentAnalysis
{
    public function __construct(
        public string $polarity,
        public float $score,
        public float $confidence,
        public string $reason,
        public string $provider,
        public string $model,
        public TokenUsage $usage = new TokenUsage,
        public float $costUsd = 0.0,
    ) {}
}
