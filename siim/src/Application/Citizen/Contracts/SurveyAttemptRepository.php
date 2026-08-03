<?php

declare(strict_types=1);

namespace SIIM\Application\Citizen\Contracts;

use DateTimeImmutable;

interface SurveyAttemptRepository
{
    public function create(string $surveyId, DateTimeImmutable $startedAt): string;

    public function startedAt(string $attemptId, string $surveyId): ?DateTimeImmutable;

    public function complete(string $attemptId, string $surveyId, string $responseId, DateTimeImmutable $completedAt): bool;

    public function discard(string $attemptId, string $surveyId): void;
}
