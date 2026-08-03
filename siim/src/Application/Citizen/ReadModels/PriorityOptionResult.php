<?php

declare(strict_types=1);

namespace SIIM\Application\Citizen\ReadModels;

final readonly class PriorityOptionResult
{
    public function __construct(
        public string $value,
        public string $label,
        public int $count,
        public float $percent,
    ) {}
}
