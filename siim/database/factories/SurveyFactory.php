<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Survey;
use Illuminate\Database\Eloquent\Factories\Factory;
use SIIM\Domain\Citizen\SurveyStatus;

/** @extends Factory<Survey> */
class SurveyFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(3),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'status' => SurveyStatus::Draft,
            'opens_at' => null,
            'closes_at' => null,
            'is_anonymous' => true,
            'created_by' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => ['status' => SurveyStatus::Published]);
    }

    public function draft(): static
    {
        return $this->state(fn (): array => ['status' => SurveyStatus::Draft]);
    }
}
