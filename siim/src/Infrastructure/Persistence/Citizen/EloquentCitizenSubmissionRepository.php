<?php

declare(strict_types=1);

namespace SIIM\Infrastructure\Persistence\Citizen;

use App\Models\Comment;
use App\Models\SurveyAnswer;
use App\Models\SurveyResponse as SurveyResponseModel;
use DateTimeImmutable;
use Illuminate\Database\QueryException;
use SIIM\Application\Citizen\Contracts\CitizenSubmissionRepository;
use SIIM\Application\Citizen\Data\SurveySubmission;
use SIIM\Domain\Citizen\Channel;
use SIIM\Domain\Citizen\Exceptions\DuplicateResponseException;

final class EloquentCitizenSubmissionRepository implements CitizenSubmissionRepository
{
    public function existsForDate(string $surveyId, string $ipHash, DateTimeImmutable $date): bool
    {
        return SurveyResponseModel::query()
            ->where('survey_id', $surveyId)
            ->where('ip_hash', $ipHash)
            ->whereDate('response_date', $date->format('Y-m-d'))
            ->exists();
    }

    public function save(SurveySubmission $submission): void
    {
        try {
            if ($submission->comment !== null) {
                Comment::query()->create([
                    'id' => $submission->comment->id,
                    'text' => $submission->comment->text,
                    'channel' => Channel::WebSurvey,
                    'source' => $submission->comment->source,
                    'language' => 'es',
                    'captured_at' => $submission->comment->capturedAt,
                ]);
            }

            $response = $submission->response;
            SurveyResponseModel::query()->create([
                'id' => $response->id,
                'survey_id' => $response->surveyId,
                'comment_id' => $response->commentId,
                'ip_hash' => $response->ipHash,
                'user_agent_hash' => $response->userAgentHash,
                'respondent_contact' => $response->respondentContact,
                'zone' => $response->zone,
                'age_range' => $response->ageRange,
                'completion_ms' => $response->completionMs,
                'submitted_at' => $response->submittedAt,
                'response_date' => $response->submittedAt->format('Y-m-d'),
            ]);

            foreach ($response->answers as $answer) {
                $value = $answer->questionId === $submission->contactQuestionId && $response->respondentContact !== null
                    ? $response->respondentContact
                    : $answer->value;
                SurveyAnswer::query()->create(array_merge([
                    'response_id' => $response->id,
                    'question_id' => $answer->questionId,
                    'value_int' => null,
                    'value_text' => null,
                    'value_json' => null,
                ], $this->answerColumns($value)));
            }
        } catch (QueryException $exception) {
            if ($this->isResponseDedupeViolation($exception)) {
                throw new DuplicateResponseException('A response has already been submitted today.', previous: $exception);
            }
            throw $exception;
        }
    }

    /** @return array{value_int: ?int, value_text: ?string, value_json: ?array<mixed>} */
    private function answerColumns(mixed $value): array
    {
        if (is_int($value)) {
            return ['value_int' => $value, 'value_text' => null, 'value_json' => null];
        }
        if (is_array($value)) {
            return ['value_int' => null, 'value_text' => null, 'value_json' => $value];
        }

        return ['value_int' => null, 'value_text' => is_string($value) ? $value : null, 'value_json' => null];
    }

    private function isResponseDedupeViolation(QueryException $exception): bool
    {
        $driverCode = $exception->errorInfo[1] ?? null;

        return $driverCode === 1062 && str_contains($exception->getMessage(), 'survey_responses_dedupe_unique');
    }
}
