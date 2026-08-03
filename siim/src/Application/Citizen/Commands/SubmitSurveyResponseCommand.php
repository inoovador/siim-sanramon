<?php

declare(strict_types=1);

namespace SIIM\Application\Citizen\Commands;

use DateTimeImmutable;

final readonly class SubmitSurveyResponseCommand
{
    /** @param array<array-key, mixed> $answersByQuestionId */
    public function __construct(
        public string $surveySlug,
        public array $answersByQuestionId,
        public string $ipAddress,
        public ?string $userAgent,
        public ?int $completionMs,
        public DateTimeImmutable $submittedAt,
    ) {}
}
