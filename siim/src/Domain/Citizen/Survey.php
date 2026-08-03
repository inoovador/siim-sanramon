<?php

declare(strict_types=1);

namespace SIIM\Domain\Citizen;

use DateTimeImmutable;
use SIIM\Domain\Citizen\Exceptions\SurveyClosedException;

final readonly class Survey
{
    /**
     * @param  list<SurveyQuestion>  $questions
     */
    public function __construct(
        public string $id,
        public string $slug,
        public string $title,
        public SurveyStatus $status,
        public ?DateTimeImmutable $opensAt = null,
        public ?DateTimeImmutable $closesAt = null,
        public array $questions = [],
        public ?string $description = null,
        public bool $isAnonymous = true,
        public ?int $createdBy = null,
    ) {}

    public function assertAcceptsResponsesAt(DateTimeImmutable $submittedAt): void
    {
        if ($this->status !== SurveyStatus::Published) {
            throw new SurveyClosedException('Survey is not published.');
        }

        if ($this->opensAt !== null && $submittedAt < $this->opensAt) {
            throw new SurveyClosedException('Survey has not opened yet.');
        }

        if ($this->closesAt !== null && $submittedAt > $this->closesAt) {
            throw new SurveyClosedException('Survey has closed.');
        }
    }
}
