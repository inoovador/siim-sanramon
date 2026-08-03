<?php

declare(strict_types=1);

namespace SIIM\Application\Citizen\ReadModels;

final readonly class SurveyExportRow
{
    /** @param list<string> $cells */
    public function __construct(public array $cells) {}
}
