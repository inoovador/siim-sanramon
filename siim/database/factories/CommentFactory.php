<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;
use SIIM\Domain\Citizen\Channel;

/** @extends Factory<Comment> */
class CommentFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'text' => fake()->paragraph(),
            'channel' => Channel::WebForm,
            'source' => 'factory',
            'external_id' => fake()->uuid(),
            'author_alias' => fake()->optional()->userName(),
            'author_contact' => null,
            'language' => 'es',
            'captured_at' => fake()->dateTimeBetween('-1 year'),
            'redacted_at' => null,
        ];
    }

    public function webSurvey(): static
    {
        return $this->state(fn (): array => [
            'channel' => Channel::WebSurvey,
            'source' => 'survey:percepcion-2026',
        ]);
    }
}
