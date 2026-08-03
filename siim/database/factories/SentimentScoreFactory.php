<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Comment;
use App\Models\SentimentScore;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SentimentScore> */
class SentimentScoreFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'comment_id' => Comment::factory(),
            'polarity' => fake()->randomElement(['positive', 'neutral', 'negative']),
            'score' => fake()->randomFloat(3, -1, 1),
            'confidence' => fake()->randomFloat(3, 0, 1),
            'reason' => fake()->optional()->sentence(),
            'analyzed_at' => now(),
        ];
    }
}
