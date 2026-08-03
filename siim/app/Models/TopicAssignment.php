<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TopicAssignmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TopicAssignment extends Model
{
    /** @use HasFactory<TopicAssignmentFactory> */
    use HasFactory;

    public $incrementing = false;

    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = ['comment_id', 'topic_id', 'confidence', 'assigned_at'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'confidence' => 'float',
            'assigned_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<Comment, $this> */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    /** @return BelongsTo<Topic, $this> */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(Topic::class);
    }
}
