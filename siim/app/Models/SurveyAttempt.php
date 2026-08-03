<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $id
 * @property string $survey_id
 * @property string|null $response_id
 * @property CarbonImmutable $started_at
 * @property CarbonImmutable|null $completed_at
 */
class SurveyAttempt extends Model
{
    use HasUuids;

    protected $dateFormat = 'Y-m-d H:i:s.u';

    /** @var list<string> */
    protected $fillable = ['id', 'survey_id', 'response_id', 'started_at', 'completed_at'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'started_at' => 'immutable_datetime',
            'completed_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<Survey, $this> */
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    /** @return BelongsTo<SurveyResponse, $this> */
    public function response(): BelongsTo
    {
        return $this->belongsTo(SurveyResponse::class);
    }
}
