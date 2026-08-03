<?php

declare(strict_types=1);

namespace SIIM\Application\Citizen\ReadModels;

final readonly class SurveyResults
{
    /**
     * @param  list<ScaleDistribution>  $scales
     * @param  list<PriorityOptionResult>  $priorities
     */
    public function __construct(
        public string $slug,
        public string $title,
        public string $status,
        public int $totalResponses,
        public ?float $generalAverage,
        public ?float $nps,
        public ?float $positiveSentimentPercent,
        public array $scales,
        public array $priorities,
        public SurveyCommentsPage $comments,
    ) {}
}
