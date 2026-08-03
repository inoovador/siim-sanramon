<?php

declare(strict_types=1);

namespace SIIM\Application\Citizen\UseCases;

use DateTimeImmutable;
use InvalidArgumentException;
use SIIM\Application\Citizen\Contracts\SurveyRepository;
use SIIM\Domain\Citizen\Survey;

final readonly class GetPublishedSurveyUseCase
{
    public function __construct(private SurveyRepository $surveys) {}

    public function handle(string $slug, DateTimeImmutable $at): Survey
    {
        $survey = $this->surveys->findPublishedBySlug($slug);
        if ($survey === null) {
            throw new InvalidArgumentException('Published survey was not found.');
        }

        $survey->assertAcceptsResponsesAt($at);

        return $survey;
    }
}
