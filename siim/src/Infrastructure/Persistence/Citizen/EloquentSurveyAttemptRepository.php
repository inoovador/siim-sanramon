<?php

declare(strict_types=1);

namespace SIIM\Infrastructure\Persistence\Citizen;

use App\Models\SurveyAttempt;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Support\Str;
use SIIM\Application\Citizen\Contracts\SurveyAttemptRepository;
use UnexpectedValueException;

final class EloquentSurveyAttemptRepository implements SurveyAttemptRepository
{
    public function create(string $surveyId, DateTimeImmutable $startedAt): string
    {
        $id = (string) Str::uuid();
        SurveyAttempt::query()->create([
            'id' => $id,
            'survey_id' => $surveyId,
            'started_at' => $startedAt,
        ]);

        return $id;
    }

    public function startedAt(string $attemptId, string $surveyId): ?DateTimeImmutable
    {
        $value = SurveyAttempt::query()
            ->whereKey($attemptId)
            ->where('survey_id', $surveyId)
            ->value('started_at');

        if ($value === null) {
            return null;
        }

        if (! $value instanceof DateTimeInterface) {
            if (! is_string($value)) {
                throw new UnexpectedValueException('Expected a date-time value for the survey attempt.');
            }

            $value = new DateTimeImmutable($value);
        }

        return DateTimeImmutable::createFromInterface($value);
    }

    public function complete(string $attemptId, string $surveyId, string $responseId, DateTimeImmutable $completedAt): bool
    {
        $attempt = SurveyAttempt::query()
            ->whereKey($attemptId)
            ->where('survey_id', $surveyId)
            ->lockForUpdate()
            ->first();

        if ($attempt === null) {
            return false;
        }

        if ($attempt->response_id !== null) {
            if ($attempt->response_id !== $responseId) {
                return false;
            }

            return true;
        }

        $attempt->forceFill([
            'response_id' => $responseId,
            'completed_at' => $completedAt,
        ])->save();

        return true;
    }

    public function discard(string $attemptId, string $surveyId): void
    {
        SurveyAttempt::query()
            ->whereKey($attemptId)
            ->where('survey_id', $surveyId)
            ->whereNull('response_id')
            ->delete();
    }
}
