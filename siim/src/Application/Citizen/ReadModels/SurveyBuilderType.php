<?php

declare(strict_types=1);

namespace SIIM\Application\Citizen\ReadModels;

final readonly class SurveyBuilderType
{
    public function __construct(
        public string $value,
        public string $label,
        public bool $requiresOptions,
    ) {}
}
