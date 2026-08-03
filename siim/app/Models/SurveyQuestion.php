<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SurveyQuestionFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use SIIM\Domain\Citizen\QuestionType;

/**
 * @property string $id
 * @property int $position
 * @property QuestionType $type
 * @property string $label
 * @property bool $is_required
 * @property list<string|array{value: int|string, label?: string}>|null $options
 * @property int|null $max_selections
 * @property int|null $max_length
 */
class SurveyQuestion extends Model
{
    /** @use HasFactory<SurveyQuestionFactory> */
    use HasFactory, HasUuids;

    /** @var list<string> */
    protected $fillable = [
        'id', 'survey_id', 'position', 'type', 'label', 'help_text', 'is_required',
        'options', 'max_selections', 'max_length', 'topic_slug',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'position' => 'integer',
            'type' => QuestionType::class,
            'is_required' => 'boolean',
            'options' => 'array',
            'max_selections' => 'integer',
            'max_length' => 'integer',
        ];
    }

    /** @return BelongsTo<Survey, $this> */
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    /** @return HasMany<SurveyAnswer, $this> */
    public function answers(): HasMany
    {
        return $this->hasMany(SurveyAnswer::class, 'question_id');
    }
}
