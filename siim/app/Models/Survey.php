<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SurveyFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use SIIM\Domain\Citizen\SurveyStatus;

/**
 * @property string $id
 * @property string $slug
 * @property string $title
 * @property SurveyStatus $status
 * @property-read Collection<int, SurveyQuestion> $questions
 */
class Survey extends Model
{
    /** @use HasFactory<SurveyFactory> */
    use HasFactory, HasUuids;

    /** @var list<string> */
    protected $fillable = [
        'id', 'slug', 'title', 'description', 'status', 'opens_at', 'closes_at',
        'is_anonymous', 'created_by',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'status' => SurveyStatus::class,
            'opens_at' => 'immutable_datetime',
            'closes_at' => 'immutable_datetime',
            'is_anonymous' => 'boolean',
        ];
    }

    /** @return HasMany<SurveyQuestion, $this> */
    public function questions(): HasMany
    {
        return $this->hasMany(SurveyQuestion::class)->orderBy('position');
    }

    /** @return HasMany<SurveyResponse, $this> */
    public function responses(): HasMany
    {
        return $this->hasMany(SurveyResponse::class);
    }

    /** @return HasMany<SurveyAttempt, $this> */
    public function attempts(): HasMany
    {
        return $this->hasMany(SurveyAttempt::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
