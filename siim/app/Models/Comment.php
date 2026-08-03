<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\CommentFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use SIIM\Domain\Citizen\Channel;

/** @property-read Collection<int, Topic> $topics */
class Comment extends Model
{
    /** @use HasFactory<CommentFactory> */
    use HasFactory, HasUuids;

    /** @var list<string> */
    protected $fillable = [
        'id', 'text', 'channel', 'source', 'external_id', 'author_alias',
        'author_contact', 'language', 'captured_at', 'redacted_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'channel' => Channel::class,
            'captured_at' => 'immutable_datetime',
            'redacted_at' => 'immutable_datetime',
        ];
    }

    /** @return HasOne<SentimentScore, $this> */
    public function sentimentScore(): HasOne
    {
        return $this->hasOne(SentimentScore::class);
    }

    /** @return BelongsToMany<Topic, $this> */
    public function topics(): BelongsToMany
    {
        return $this->belongsToMany(Topic::class, 'topic_assignments')
            ->withPivot(['confidence', 'assigned_at']);
    }

    /** @return HasMany<AnalysisRun, $this> */
    public function analysisRuns(): HasMany
    {
        return $this->hasMany(AnalysisRun::class);
    }
}
