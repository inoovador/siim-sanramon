<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AnalysisRun;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<AnalysisRun> */
class AnalysisRunFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'comment_id' => Comment::factory(),
            'llm_provider' => 'fake',
            'llm_model' => 'fake-model',
            'status' => 'success',
            'fallback_used' => false,
            'tokens_input' => fake()->numberBetween(10, 1000),
            'tokens_output' => fake()->numberBetween(10, 1000),
            'cost_usd' => fake()->randomFloat(6, 0, 1),
            'error_category' => null,
            'error_message' => null,
            'requested_at' => now(),
            'completed_at' => now(),
        ];
    }
}
