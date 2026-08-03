<?php

declare(strict_types=1);

namespace SIIM\Application\Citizen\ReadModels;

final readonly class SurveyExportDefinition
{
    /** @param list<string> $headers */
    public function __construct(
        public string $slug,
        public array $headers,
    ) {}
}
