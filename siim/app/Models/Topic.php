<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\TopicFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Topic extends Model
{
    /** @use HasFactory<TopicFactory> */
    use HasFactory, HasUuids;

    /** @var list<string> */
    protected $fillable = ['id', 'slug', 'label', 'description', 'is_active'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** @return BelongsToMany<Comment, $this> */
    public function comments(): BelongsToMany
    {
        return $this->belongsToMany(Comment::class, 'topic_assignments')
            ->withPivot(['confidence', 'assigned_at']);
    }
}
