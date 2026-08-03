<?php

declare(strict_types=1);

namespace SIIM\Infrastructure\Persistence\Citizen;

use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use SIIM\Application\Citizen\Contracts\SurveyContactAccessRepository;

final class EloquentSurveyContactAccessRepository implements SurveyContactAccessRepository
{
    public function record(
        string $surveyId,
        string $responseId,
        int $userId,
        DateTimeImmutable $accessedAt,
        ?string $requestHash,
    ): void {
        DB::table('survey_contact_access_logs')->insert([
            'id' => (string) Str::uuid(),
            'survey_id' => $surveyId,
            'response_id' => $responseId,
            'user_id' => $userId,
            'accessed_at' => $accessedAt,
            'request_hash' => $requestHash,
            'created_at' => $accessedAt,
            'updated_at' => $accessedAt,
        ]);
    }
}
