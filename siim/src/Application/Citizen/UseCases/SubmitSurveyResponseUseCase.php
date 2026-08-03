<?php

declare(strict_types=1);

namespace SIIM\Application\Citizen\UseCases;

use InvalidArgumentException;
use SIIM\Application\Citizen\Commands\SubmitSurveyResponseCommand;
use SIIM\Application\Citizen\Contracts\CitizenEventPublisher;
use SIIM\Application\Citizen\Contracts\CitizenSubmissionRepository;
use SIIM\Application\Citizen\Contracts\CitizenTransaction;
use SIIM\Application\Citizen\Contracts\ContactEncryptor;
use SIIM\Application\Citizen\Contracts\SurveyAttemptRepository;
use SIIM\Application\Citizen\Contracts\SurveyRepository;
use SIIM\Application\Citizen\Data\CommentDraft;
use SIIM\Application\Citizen\Data\SurveySubmission;
use SIIM\Application\Citizen\Results\SubmitSurveyResponseResult;
use SIIM\Domain\Citizen\Answer;
use SIIM\Domain\Citizen\Events\CommentIngested;
use SIIM\Domain\Citizen\Exceptions\DuplicateResponseException;
use SIIM\Domain\Citizen\SurveyQuestion;
use SIIM\Domain\Citizen\SurveyResponse;

final readonly class SubmitSurveyResponseUseCase
{
    public function __construct(
        private SurveyRepository $surveys,
        private CitizenSubmissionRepository $submissions,
        private CitizenTransaction $transaction,
        private ContactEncryptor $contacts,
        private CitizenEventPublisher $events,
        private SurveyAttemptRepository $attempts,
        private string $appKey,
    ) {}

    public function handle(SubmitSurveyResponseCommand $command): SubmitSurveyResponseResult
    {
        $survey = $this->surveys->findBySlug($command->surveySlug);
        if ($survey === null) {
            throw new InvalidArgumentException('Survey was not found.');
        }

        $answers = array_map(static fn (mixed $value, string $id): Answer => new Answer($id, $value), $command->answersByQuestionId, array_keys($command->answersByQuestionId));
        $ipHash = hash('sha256', $command->ipAddress . $this->appKey);
        $userAgentHash = $command->userAgent === null ? null : hash_hmac('sha256', $command->userAgent, $this->appKey);
        $contactQuestionId = $this->questionIdAtPosition($survey->questions, 12);
        $commentQuestionId = $this->questionIdAtPosition($survey->questions, 11);
        $rawContact = $contactQuestionId === null ? null : ($command->answersByQuestionId[$contactQuestionId] ?? null);
        $contact = is_string($rawContact) && trim($rawContact) !== '' ? $this->contacts->encrypt(trim($rawContact)) : null;
        $commentText = $commentQuestionId === null ? null : ($command->answersByQuestionId[$commentQuestionId] ?? null);
        $comment = is_string($commentText) && trim($commentText) !== ''
            ? new CommentDraft($this->uuid(), trim($commentText), '', $command->submittedAt)
            : null;

        $response = SurveyResponse::submit(
            $this->uuid(),
            $survey,
            $answers,
            $command->submittedAt,
            $ipHash,
            $userAgentHash,
            $contact,
            $this->answerString($command, $survey->questions, 1),
            $this->answerString($command, $survey->questions, 2),
            $command->completionMs,
            $comment?->id,
        );
        $comment = $comment === null ? null : new CommentDraft($comment->id, $comment->text, "survey:{$survey->slug}:{$response->id}", $comment->capturedAt);

        $this->transaction->run(function () use ($survey, $ipHash, $command, $response, $contactQuestionId, $comment): void {
            if ($this->submissions->existsForDate($survey->id, $ipHash, $command->submittedAt)) {
                throw new DuplicateResponseException('A response has already been submitted today.');
            }

            $this->submissions->save(new SurveySubmission($response, $contactQuestionId, $comment));

            if ($command->attemptId !== null && ! $this->attempts->complete($command->attemptId, $survey->id, $response->id, $command->submittedAt)) {
                throw new InvalidArgumentException('Survey attempt was not found or has already been completed.');
            }
        });

        if ($comment !== null) {
            $this->events->publish(new CommentIngested($comment->id));
        }

        return new SubmitSurveyResponseResult($response->id, $comment?->id);
    }

    /** @param list<SurveyQuestion> $questions */
    private function questionIdAtPosition(array $questions, int $position): ?string
    {
        foreach ($questions as $question) {
            if ($question->position === $position) {
                return $question->id;
            }
        }

        return null;
    }

    /** @param list<SurveyQuestion> $questions */
    private function answerString(SubmitSurveyResponseCommand $command, array $questions, int $position): ?string
    {
        $id = $this->questionIdAtPosition($questions, $position);
        $value = $id === null ? null : ($command->answersByQuestionId[$id] ?? null);

        return is_string($value) ? $value : null;
    }

    private function uuid(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0F) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3F) | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }
}
