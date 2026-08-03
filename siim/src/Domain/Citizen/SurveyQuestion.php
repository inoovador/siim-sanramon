<?php

declare(strict_types=1);

namespace SIIM\Domain\Citizen;

use InvalidArgumentException;

final readonly class SurveyQuestion
{
    /**
     * @param  list<string|array{value: int|string, label?: string}>  $options
     */
    public function __construct(
        public string $id,
        public int $position,
        public QuestionType $type,
        public string $label,
        public bool $isRequired = true,
        public array $options = [],
        public ?int $maxSelections = null,
        public ?int $maxLength = null,
        public ?string $helpText = null,
        public ?string $topicSlug = null,
    ) {}

    public function validate(Answer $answer): void
    {
        if ($answer->questionId !== $this->id) {
            throw new InvalidArgumentException('Answer does not belong to this question.');
        }

        match ($this->type) {
            QuestionType::Scale1To5 => $this->validateIntegerRange($answer->value, 1, 5),
            QuestionType::Nps => $this->validateIntegerRange($answer->value, 0, 10),
            QuestionType::SingleChoice => $this->validateSingleChoice($answer->value),
            QuestionType::MultiChoice => $this->validateMultiChoice($answer->value),
            QuestionType::OpenText => $this->validateOpenText($answer->value),
        };
    }

    private function validateIntegerRange(mixed $value, int $minimum, int $maximum): void
    {
        if (! is_int($value) || $value < $minimum || $value > $maximum) {
            throw new InvalidArgumentException("Answer must be an integer from {$minimum} to {$maximum}.");
        }
    }

    private function validateSingleChoice(mixed $value): void
    {
        if (! is_string($value)) {
            throw new InvalidArgumentException('Single choice answer must be a string.');
        }

        if (! in_array($value, $this->configuredValues(), true)) {
            throw new InvalidArgumentException('Single choice answer must be a configured option.');
        }
    }

    private function validateMultiChoice(mixed $value): void
    {
        if (! is_array($value) || ! array_is_list($value)) {
            throw new InvalidArgumentException('Multi choice answer must be an array.');
        }

        foreach ($value as $choice) {
            if (! is_string($choice)) {
                throw new InvalidArgumentException('Multi choice answer must contain strings only.');
            }
        }

        if (count(array_unique($value)) !== count($value)) {
            throw new InvalidArgumentException('Multi choice answer values must be unique.');
        }

        if ($this->isRequired && $value === []) {
            throw new InvalidArgumentException('Required multi choice answer must not be empty.');
        }

        if ($this->maxSelections !== null && count($value) > $this->maxSelections) {
            throw new InvalidArgumentException("Multi choice answer may contain at most {$this->maxSelections} selections.");
        }

        if (array_diff($value, $this->configuredValues()) !== []) {
            throw new InvalidArgumentException('Multi choice answer must contain configured options only.');
        }
    }

    private function validateOpenText(mixed $value): void
    {
        if (! is_string($value)) {
            throw new InvalidArgumentException('Open text answer must be a string.');
        }

        if ($this->isRequired && $value === '') {
            throw new InvalidArgumentException('Required open text answer must not be empty.');
        }

        if ($this->maxLength !== null && mb_strlen($value) > $this->maxLength) {
            throw new InvalidArgumentException("Open text answer may contain at most {$this->maxLength} characters.");
        }
    }

    /** @return list<string> */
    private function configuredValues(): array
    {
        return array_map(
            static fn (string|array $option): string => is_string($option) ? $option : (string) $option['value'],
            $this->options,
        );
    }
}
