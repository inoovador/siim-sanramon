<?php

declare(strict_types=1);

namespace SIIM\Application\Citizen\ReadModels;

final readonly class SurveyFilterOptions
{
    /**
     * @param  list<string>  $zones
     * @param  list<string>  $ageRanges
     */
    public function __construct(
        public string $slug,
        public string $title,
        public string $status,
        public array $zones,
        public array $ageRanges,
    ) {}
}
