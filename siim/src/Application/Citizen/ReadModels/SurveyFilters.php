<?php

declare(strict_types=1);

namespace SIIM\Application\Citizen\ReadModels;

use DateTimeImmutable;

final readonly class SurveyFilters
{
    public function __construct(
        public ?DateTimeImmutable $from = null,
        public ?DateTimeImmutable $to = null,
        public ?string $zone = null,
        public ?string $ageRange = null,
        public ?string $search = null,
        public int $page = 1,
        public int $perPage = 15,
    ) {}
}
