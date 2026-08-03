<?php

declare(strict_types=1);

namespace SIIM\Application\Citizen\ReadModels;

use DateTimeImmutable;

final readonly class SurveyComment
{
    public function __construct(
        public string $responseId,
        public string $text,
        public ?string $polarity,
        public DateTimeImmutable $submittedAt,
        public bool $contactProvided,
    ) {}
}
