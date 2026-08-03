<?php

declare(strict_types=1);

namespace SIIM\Application\Citizen\Commands;

use DateTimeImmutable;

final readonly class CreateSurveyCommand
{
    /** @param list<CreateSurveyQuestion> $questions */
    public function __construct(
        public string $title,
        public string $slug,
        public ?string $description,
        public ?DateTimeImmutable $opensAt,
        public ?DateTimeImmutable $closesAt,
        public int $createdBy,
        public array $questions,
    ) {}
}
