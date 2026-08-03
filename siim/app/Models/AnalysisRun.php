<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AnalysisRunFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnalysisRun extends Model
{
    /** @use HasFactory<AnalysisRunFactory> */
    use HasFactory, HasUuids;

    public $timestamps = false;

    /** @var list<string> */
    protected $fillable = [
        'id', 'comment_id', 'llm_provider', 'llm_model', 'status', 'fallback_used',
        'tokens_input', 'tokens_output', 'cost_usd', 'error_category', 'error_message',
        'requested_at', 'completed_at',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'tokens_input' => 'integer',
            'tokens_output' => 'integer',
            'fallback_used' => 'boolean',
            'cost_usd' => 'decimal:6',
            'requested_at' => 'immutable_datetime',
            'completed_at' => 'immutable_datetime',
        ];
    }

    /** @return BelongsTo<Comment, $this> */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }
}
