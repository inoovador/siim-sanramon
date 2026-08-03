<?php

declare(strict_types=1);

namespace SIIM\Application\Citizen\ReadModels;

use DateTimeImmutable;

final readonly class SurveyOverview
{
    public function __construct(
        public string $id,
        public string $slug,
        public string $title,
        public string $status,
        public int $responseCount,
        public ?DateTimeImmutable $lastResponseAt,
        public ?float $completionRate,
    ) {}
}
