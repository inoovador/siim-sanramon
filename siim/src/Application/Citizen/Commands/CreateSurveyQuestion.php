<?php

declare(strict_types=1);

namespace SIIM\Application\Citizen\Commands;

final readonly class CreateSurveyQuestion
{
    /** @param list<string> $options */
    public function __construct(
        public string $label,
        public string $type,
        public bool $isRequired,
        public array $options = [],
        public ?int $maxSelections = null,
        public ?int $maxLength = null,
        public ?string $helpText = null,
    ) {}
}
