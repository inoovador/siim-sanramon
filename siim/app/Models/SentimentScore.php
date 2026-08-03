<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\SentimentScoreFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SentimentScore extends Model
{
    /** @use HasFactory<SentimentScoreFactory> */
    use HasFactory;

    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = 'comment_id';

    protected $keyType = 'string';

    /** @var list<string> */
    protected $fillable = ['comment_id', 'polarity', 'score', 'confidence', 'reason', 'analyzed_at'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'score' => 'float',
            'confidence' => 'float',
            'analyzed_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<Comment, $this> */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }
}
