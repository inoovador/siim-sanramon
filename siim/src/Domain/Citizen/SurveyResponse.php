<?php

declare(strict_types=1);

namespace SIIM\Domain\Citizen;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class SurveyResponse
{
    /**
     * @param  list<Answer>  $answers
     */
    private function __construct(
        public string $id,
        public string $surveyId,
        public array $answers,
        public DateTimeImmutable $submittedAt,
        public string $ipHash,
        public ?string $userAgentHash = null,
        public ?string $respondentContact = null,
        public ?string $zone = null,
        public ?string $ageRange = null,
        public ?int $completionMs = null,
        public ?string $commentId = null,
    ) {}

    /**
     * @param  list<Answer>  $answers
     */
    public static function submit(
        string $id,
        Survey $survey,
        array $answers,
        DateTimeImmutable $submittedAt,
        string $ipHash,
        ?string $userAgentHash = null,
        ?string $respondentContact = null,
        ?string $zone = null,
        ?string $ageRange = null,
        ?int $completionMs = null,
        ?string $commentId = null,
    ): self {
        $survey->assertAcceptsResponsesAt($submittedAt);
        self::validateAnswers($survey, $answers);

        return new self(
            id: $id,
            surveyId: $survey->id,
            answers: $answers,
            submittedAt: $submittedAt,
            ipHash: $ipHash,
            userAgentHash: $userAgentHash,
            respondentContact: $respondentContact,
            zone: $zone,
            ageRange: $ageRange,
            completionMs: $completionMs,
            commentId: $commentId,
        );
    }

    /**
     * @param  list<Answer>  $answers
     */
    private static function validateAnswers(Survey $survey, array $answers): void
    {
        $answersByQuestion = [];

        foreach ($answers as $answer) {
            if (isset($answersByQuestion[$answer->questionId])) {
                throw new InvalidArgumentException("Duplicate answer for question {$answer->questionId}.");
            }

            $answersByQuestion[$answer->questionId] = $answer;
        }

        foreach ($survey->questions as $question) {
            $answer = $answersByQuestion[$question->id] ?? null;

            if ($answer === null) {
                if ($question->isRequired) {
                    throw new InvalidArgumentException("Missing required answer for question {$question->id}.");
                }

                continue;
            }

            $question->validate($answer);
            unset($answersByQuestion[$question->id]);
        }

        if ($answersByQuestion !== []) {
            throw new InvalidArgumentException('Response contains an answer for an unknown question.');
        }
    }
}
