<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Survey;
use App\Models\SurveyQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;
use SIIM\Domain\Citizen\QuestionType;

/** @extends Factory<SurveyQuestion> */
class SurveyQuestionFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'survey_id' => Survey::factory(),
            'position' => fake()->unique()->numberBetween(1, 65000),
            'type' => QuestionType::Scale1To5,
            'label' => fake()->sentence(),
            'help_text' => null,
            'is_required' => true,
            'options' => null,
            'max_selections' => null,
            'max_length' => null,
            'topic_slug' => null,
        ];
    }
}
