<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Survey;
use App\Models\SurveyResponse;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SurveyResponse> */
class SurveyResponseFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        $submittedAt = fake()->dateTimeBetween('-1 year');

        return [
            'survey_id' => Survey::factory(),
            'comment_id' => null,
            'ip_hash' => hash('sha256', fake()->uuid()),
            'user_agent_hash' => hash('sha256', fake()->userAgent()),
            'respondent_contact' => null,
            'zone' => fake()->optional()->randomElement(['Centro', 'Norte', 'Sur']),
            'age_range' => fake()->optional()->randomElement(['18-25', '26-35', '36-50']),
            'completion_ms' => fake()->numberBetween(1000, 120000),
            'submitted_at' => $submittedAt,
            'response_date' => $submittedAt->format('Y-m-d'),
        ];
    }
}
