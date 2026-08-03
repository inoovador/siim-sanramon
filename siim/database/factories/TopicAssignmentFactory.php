<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Topic;
use App\Models\TopicAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TopicAssignment> */
class TopicAssignmentFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'comment_id' => Comment::factory(),
            'topic_id' => Topic::factory(),
            'confidence' => fake()->randomFloat(3, 0, 1),
            'assigned_at' => now(),
        ];
    }
}
