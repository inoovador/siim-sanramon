<?php

declare(strict_types=1);

namespace SIIM\Application\Citizen\UseCases;

use DateTimeImmutable;
use SIIM\Application\Citizen\Contracts\SurveyAttemptRepository;

final readonly class TrackSurveyAttemptUseCase
{
    public function __construct(private SurveyAttemptRepository $attempts) {}

    public function start(string $surveyId, DateTimeImmutable $startedAt): string
    {
        return $this->attempts->create($surveyId, $startedAt);
    }

    public function elapsedMilliseconds(string $attemptId, string $surveyId, DateTimeImmutable $now): ?int
    {
        $startedAt = $this->attempts->startedAt($attemptId, $surveyId);
        if ($startedAt === null) {
            return null;
        }

        return max(0, (int) round(((float) $now->format('U.u') - (float) $startedAt->format('U.u')) * 1000));
    }

    public function discard(string $attemptId, string $surveyId): void
    {
        $this->attempts->discard($attemptId, $surveyId);
    }
}
