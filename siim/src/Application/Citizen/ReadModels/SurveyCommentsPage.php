<?php

declare(strict_types=1);

namespace SIIM\Application\Citizen\ReadModels;

final readonly class SurveyCommentsPage
{
    /** @param list<SurveyComment> $items */
    public function __construct(
        public array $items,
        public int $currentPage,
        public int $lastPage,
        public int $total,
    ) {}
}
