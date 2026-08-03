<?php

declare(strict_types=1);

use SIIM\Domain\Citizen\Answer;
use SIIM\Domain\Citizen\Channel;
use SIIM\Domain\Citizen\Exceptions\SurveyClosedException;
use SIIM\Domain\Citizen\QuestionType;
use SIIM\Domain\Citizen\Survey;
use SIIM\Domain\Citizen\SurveyQuestion;
use SIIM\Domain\Citizen\SurveyResponse;
use SIIM\Domain\Citizen\SurveyStatus;

/** @param list<SurveyQuestion> $questions */
function citizenSurvey(
    SurveyStatus $status = SurveyStatus::Published,
    ?DateTimeImmutable $opensAt = null,
    ?DateTimeImmutable $closesAt = null,
    array $questions = [],
): Survey {
    return new Survey(
        id: '018f47ef-56fd-7bb1-91ec-123456789abc',
        slug: 'percepcion-2026',
        title: 'Encuesta ciudadana',
        status: $status,
        opensAt: $opensAt,
        closesAt: $closesAt,
        questions: $questions,
    );
}

/** @param list<string|array{value: string, label?: string}> $options */
function citizenQuestion(
    QuestionType $type,
    bool $required = true,
    array $options = [],
    ?int $maxSelections = null,
    ?int $maxLength = null,
    string $id = '018f47ef-56fd-7bb1-91ec-123456789001',
): SurveyQuestion {
    return new SurveyQuestion(
        id: $id,
        position: 1,
        type: $type,
        label: 'Pregunta',
        isRequired: $required,
        options: $options,
        maxSelections: $maxSelections,
        maxLength: $maxLength,
    );
}

/** @param list<Answer> $answers */
function submitCitizenSurvey(Survey $survey, array $answers, ?DateTimeImmutable $at = null): SurveyResponse
{
    return SurveyResponse::submit(
        id: '018f47ef-56fd-7bb1-91ec-123456789def',
        survey: $survey,
        answers: $answers,
        submittedAt: $at ?? new DateTimeImmutable('2026-06-01 12:00:00'),
        ipHash: str_repeat('a', 64),
    );
}

it('rejects responses unless the survey is published', function (): void {
    foreach ([SurveyStatus::Draft, SurveyStatus::Closed] as $status) {
        expect(fn () => submitCitizenSurvey(citizenSurvey(status: $status), []))
            ->toThrow(SurveyClosedException::class);
    }
});

it('enforces the survey response window while accepting exact and open bounds', function (): void {
    $opens = new DateTimeImmutable('2026-06-01 08:00:00');
    $closes = new DateTimeImmutable('2026-06-30 18:00:00');
    $bounded = citizenSurvey(opensAt: $opens, closesAt: $closes);

    expect(fn () => submitCitizenSurvey($bounded, [], $opens->modify('-1 second')))
        ->toThrow(SurveyClosedException::class)
        ->and(fn () => submitCitizenSurvey($bounded, [], $closes->modify('+1 second')))
        ->toThrow(SurveyClosedException::class)
        ->and(submitCitizenSurvey($bounded, [], $opens))->toBeInstanceOf(SurveyResponse::class)
        ->and(submitCitizenSurvey($bounded, [], $closes))->toBeInstanceOf(SurveyResponse::class)
        ->and(submitCitizenSurvey(citizenSurvey(opensAt: null, closesAt: null), []))
        ->toBeInstanceOf(SurveyResponse::class);
});

it('rejects a missing required question', function (): void {
    $question = citizenQuestion(QuestionType::OpenText);

    submitCitizenSurvey(citizenSurvey(questions: [$question]), []);
})->throws(InvalidArgumentException::class, 'Missing required answer');

it('accepts only integers from one through five for scale questions', function (): void {
    $question = citizenQuestion(QuestionType::Scale1To5);
    $survey = citizenSurvey(questions: [$question]);

    foreach ([1, 2, 3, 4, 5] as $value) {
        expect(submitCitizenSurvey($survey, [new Answer($question->id, $value)]))
            ->toBeInstanceOf(SurveyResponse::class);
    }

    foreach ([0, 6, 3.0, '3'] as $value) {
        expect(fn () => submitCitizenSurvey($survey, [new Answer($question->id, $value)]))
            ->toThrow(InvalidArgumentException::class, 'integer from 1 to 5');
    }
});

it('accepts only integers from zero through ten for nps questions', function (): void {
    $question = citizenQuestion(QuestionType::Nps);
    $survey = citizenSurvey(questions: [$question]);

    foreach ([0, 5, 10] as $value) {
        expect(submitCitizenSurvey($survey, [new Answer($question->id, $value)]))
            ->toBeInstanceOf(SurveyResponse::class);
    }

    foreach ([-1, 11, 5.0, '5'] as $value) {
        expect(fn () => submitCitizenSurvey($survey, [new Answer($question->id, $value)]))
            ->toThrow(InvalidArgumentException::class, 'integer from 0 to 10');
    }
});

it('accepts one configured option only for single choice questions', function (): void {
    $question = citizenQuestion(QuestionType::SingleChoice, options: [
        ['value' => 'norte', 'label' => 'Norte'],
        ['value' => 'sur', 'label' => 'Sur'],
    ]);
    $survey = citizenSurvey(questions: [$question]);

    expect(submitCitizenSurvey($survey, [new Answer($question->id, 'norte')]))
        ->toBeInstanceOf(SurveyResponse::class)
        ->and(fn () => submitCitizenSurvey($survey, [new Answer($question->id, 'este')]))
        ->toThrow(InvalidArgumentException::class, 'configured option')
        ->and(fn () => submitCitizenSurvey($survey, [new Answer($question->id, ['norte'])]))
        ->toThrow(InvalidArgumentException::class, 'string');
});

it('validates unique configured multi choice values and maximum selections', function (): void {
    $question = citizenQuestion(
        QuestionType::MultiChoice,
        options: ['seguridad', 'salud', 'transporte'],
        maxSelections: 2,
    );
    $survey = citizenSurvey(questions: [$question]);

    expect(submitCitizenSurvey($survey, [new Answer($question->id, ['seguridad', 'salud'])]))
        ->toBeInstanceOf(SurveyResponse::class)
        ->and(fn () => submitCitizenSurvey($survey, [new Answer($question->id, ['seguridad', 'seguridad'])]))
        ->toThrow(InvalidArgumentException::class, 'unique')
        ->and(fn () => submitCitizenSurvey($survey, [new Answer($question->id, ['seguridad', 'otro'])]))
        ->toThrow(InvalidArgumentException::class, 'configured options')
        ->and(fn () => submitCitizenSurvey($survey, [new Answer($question->id, ['seguridad', 'salud', 'transporte'])]))
        ->toThrow(InvalidArgumentException::class, 'at most 2');
});

it('validates open text length with multibyte characters and optional emptiness', function (): void {
    $optional = citizenQuestion(QuestionType::OpenText, required: false, maxLength: 3);
    $survey = citizenSurvey(questions: [$optional]);

    expect(submitCitizenSurvey($survey, []))->toBeInstanceOf(SurveyResponse::class)
        ->and(submitCitizenSurvey($survey, [new Answer($optional->id, '')]))
        ->toBeInstanceOf(SurveyResponse::class)
        ->and(submitCitizenSurvey($survey, [new Answer($optional->id, 'áéí')]))
        ->toBeInstanceOf(SurveyResponse::class)
        ->and(fn () => submitCitizenSurvey($survey, [new Answer($optional->id, 'áéíó')]))
        ->toThrow(InvalidArgumentException::class, 'at most 3 characters');
});

it('rejects wrong answer shapes with a clear domain error', function (): void {
    $invalidAnswers = [
        [QuestionType::Scale1To5, [3], 'integer'],
        [QuestionType::Nps, true, 'integer'],
        [QuestionType::SingleChoice, 1, 'string'],
        [QuestionType::MultiChoice, 'a', 'array'],
        [QuestionType::OpenText, ['text'], 'string'],
    ];

    foreach ($invalidAnswers as [$type, $value, $message]) {
        $question = citizenQuestion($type, options: ['a', 'b']);

        expect(fn () => submitCitizenSurvey(
            citizenSurvey(questions: [$question]),
            [new Answer($question->id, $value)],
        ))->toThrow(InvalidArgumentException::class, $message);
    }
});

it('serializes the web survey channel as its backed value', function (): void {
    expect(Channel::WebSurvey->value)->toBe('web_survey')
        ->and(json_encode(Channel::WebSurvey, JSON_THROW_ON_ERROR))->toBe('"web_survey"');
});
