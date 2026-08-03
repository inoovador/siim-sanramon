<?php

declare(strict_types=1);

namespace SIIM\Application\Citizen\Contracts;

use SIIM\Domain\Citizen\Survey;

interface SurveyRepository
{
    public function findBySlug(string $slug): ?Survey;

    public function findPublishedBySlug(string $slug): ?Survey;

    public function save(Survey $survey): void;
}
