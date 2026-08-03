<?php

declare(strict_types=1);

namespace SIIM\Application\Citizen\Queries;

use DateTimeImmutable;
use SIIM\Application\Citizen\ReadModels\EncryptedSurveyContact;
use SIIM\Application\Citizen\ReadModels\SurveyExportDefinition;
use SIIM\Application\Citizen\ReadModels\SurveyExportRow;
use SIIM\Application\Citizen\ReadModels\SurveyFilterOptions;
use SIIM\Application\Citizen\ReadModels\SurveyFilters;
use SIIM\Application\Citizen\ReadModels\SurveyOverview;
use SIIM\Application\Citizen\ReadModels\SurveyResults;

interface SurveyResultsQuery
{
    /** @return list<SurveyOverview> */
    public function surveys(): array;

    public function activeSurvey(DateTimeImmutable $at): ?SurveyOverview;

    public function filterOptions(string $slug): ?SurveyFilterOptions;

    public function results(string $slug, SurveyFilters $filters): ?SurveyResults;

    public function exportDefinition(string $slug): ?SurveyExportDefinition;

    /** @return iterable<SurveyExportRow> */
    public function exportRows(string $slug, SurveyFilters $filters): iterable;

    public function encryptedContact(string $slug, string $responseId): ?EncryptedSurveyContact;
}
