<?php

declare(strict_types=1);

namespace SIIM\Application\Citizen\UseCases;

use InvalidArgumentException;
use SIIM\Application\Citizen\Commands\CreateSurveyCommand;
use SIIM\Application\Citizen\Commands\CreateSurveyQuestion;
use SIIM\Application\Citizen\Contracts\SurveyRepository;
use SIIM\Application\Citizen\ReadModels\SurveyBuilderType;
use SIIM\Domain\Citizen\QuestionType;
use SIIM\Domain\Citizen\Survey;
use SIIM\Domain\Citizen\SurveyQuestion;
use SIIM\Domain\Citizen\SurveyStatus;

final readonly class CreateSurveyUseCase
{
    public function __construct(private SurveyRepository $surveys) {}

    /** @return list<SurveyBuilderType> */
    public function supportedTypes(): array
    {
        return array_map(
            static fn (QuestionType $type): SurveyBuilderType => new SurveyBuilderType(
                $type->value,
                match ($type) {
                    QuestionType::Scale1To5 => 'Escala de 1 a 5',
                    QuestionType::SingleChoice => 'Selección única',
                    QuestionType::MultiChoice => 'Selección múltiple',
                    QuestionType::OpenText => 'Texto libre',
                    QuestionType::Nps => 'NPS de 0 a 10',
                },
                in_array($type, [QuestionType::SingleChoice, QuestionType::MultiChoice], true),
            ),
            QuestionType::cases(),
        );
    }

    public function handle(CreateSurveyCommand $command): Survey
    {
        $title = trim($command->title);
        $slug = trim($command->slug);
        $description = $command->description === null ? null : trim($command->description);

        if (mb_strlen($title) < 3 || mb_strlen($title) > 200) {
            throw new InvalidArgumentException('Survey title must contain between 3 and 200 characters.');
        }
        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) || mb_strlen($slug) > 64) {
            throw new InvalidArgumentException('Survey slug is invalid.');
        }
        if ($description !== null && mb_strlen($description) > 2000) {
            throw new InvalidArgumentException('Survey description is too long.');
        }
        if ($command->opensAt !== null && $command->closesAt !== null && $command->opensAt >= $command->closesAt) {
            throw new InvalidArgumentException('Survey closing date must be after its opening date.');
        }
        if ($command->questions === []) {
            throw new InvalidArgumentException('Survey requires at least one question.');
        }
        if ($this->surveys->findBySlug($slug) !== null) {
            throw new InvalidArgumentException('Survey slug is already in use.');
        }

        $questions = [];
        foreach ($command->questions as $index => $input) {
            $questions[] = $this->question($input, $index + 1);
        }

        $survey = new Survey(
            id: $this->uuid(),
            slug: $slug,
            title: $title,
            status: SurveyStatus::Draft,
            opensAt: $command->opensAt,
            closesAt: $command->closesAt,
            questions: $questions,
            description: $description === '' ? null : $description,
            isAnonymous: true,
            createdBy: $command->createdBy,
        );

        $this->surveys->save($survey);

        return $survey;
    }

    private function question(CreateSurveyQuestion $input, int $position): SurveyQuestion
    {
        $label = trim($input->label);
        $type = QuestionType::tryFrom($input->type);
        if ($type === null || $label === '' || mb_strlen($label) > 300) {
            throw new InvalidArgumentException('Survey question is invalid.');
        }

        $options = array_values(array_unique(array_filter(
            array_map(static fn (string $option): string => trim($option), $input->options),
            static fn (string $option): bool => $option !== '',
        )));
        $choice = in_array($type, [QuestionType::SingleChoice, QuestionType::MultiChoice], true);
        if ($choice && $options === []) {
            throw new InvalidArgumentException('Choice questions require options.');
        }
        if (! $choice && $options !== []) {
            throw new InvalidArgumentException('Only choice questions accept options.');
        }
        if ($type === QuestionType::MultiChoice) {
            if ($input->maxSelections === null || $input->maxSelections < 1 || $input->maxSelections > count($options)) {
                throw new InvalidArgumentException('Multi-choice selection limit is invalid.');
            }
        } elseif ($input->maxSelections !== null) {
            throw new InvalidArgumentException('Selection limit belongs to multi-choice questions only.');
        }
        if ($type === QuestionType::OpenText) {
            if ($input->maxLength === null || $input->maxLength < 1 || $input->maxLength > 5000) {
                throw new InvalidArgumentException('Open-text length limit is invalid.');
            }
        } elseif ($input->maxLength !== null) {
            throw new InvalidArgumentException('Length limit belongs to open-text questions only.');
        }

        return new SurveyQuestion(
            id: $this->uuid(),
            position: $position,
            type: $type,
            label: $label,
            isRequired: $input->isRequired,
            options: $options,
            maxSelections: $input->maxSelections,
            maxLength: $input->maxLength,
            helpText: $input->helpText === null ? null : trim($input->helpText),
        );
    }

    private function uuid(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0F) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3F) | 0x80);
        $hex = bin2hex($bytes);

        return sprintf('%s-%s-%s-%s-%s', substr($hex, 0, 8), substr($hex, 8, 4), substr($hex, 12, 4), substr($hex, 16, 4), substr($hex, 20));
    }
}
