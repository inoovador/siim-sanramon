<?php

declare(strict_types=1);

namespace SIIM\Application\Citizen\Contracts;

use DateTimeImmutable;
use SIIM\Application\Citizen\Data\SurveySubmission;

interface CitizenSubmissionRepository
{
    public function existsForDate(string $surveyId, string $ipHash, DateTimeImmutable $date): bool;

    public function save(SurveySubmission $submission): void;
}
