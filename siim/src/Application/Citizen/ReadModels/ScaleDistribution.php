<?php

declare(strict_types=1);

namespace SIIM\Application\Citizen\ReadModels;

final readonly class ScaleDistribution
{
    /** @param array<int, int> $buckets */
    public function __construct(
        public string $questionId,
        public int $position,
        public string $label,
        public array $buckets,
        public ?float $average,
        public int $answers,
    ) {}
}
