<?php

declare(strict_types=1);

namespace SIIM\Application\Citizen\Contracts;

use DateTimeImmutable;

interface SurveyContactAccessRepository
{
    public function record(
        string $surveyId,
        string $responseId,
        int $userId,
        DateTimeImmutable $accessedAt,
        ?string $requestHash,
    ): void;
}
