<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SurveyAnswer;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SurveyAnswer> */
class SurveyAnswerFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'response_id' => SurveyResponse::factory(),
            'question_id' => SurveyQuestion::factory(),
            'value_int' => fake()->numberBetween(1, 5),
            'value_text' => null,
            'value_json' => null,
        ];
    }
}
