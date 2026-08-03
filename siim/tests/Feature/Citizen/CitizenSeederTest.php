<?php

declare(strict_types=1);

use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\Topic;
use Database\Seeders\SurveySeeder;
use Database\Seeders\TopicSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function citizenSeederString(mixed $value): string
{
    assert(is_string($value));

    return $value;
}

it('seeds the exact citizen topics and survey content idempotently', function (): void {
    $expectedTopicSlugs = [
        'obras_publicas',
        'seguridad',
        'salud',
        'educacion',
        'transporte',
        'medio_ambiente',
        'limpieza',
        'tributos',
    ];
    $expectedQuestions = [
        [1, 'single_choice', '¿En qué zona del distrito vives?', true, ['Centro', 'Norte', 'Sur', 'Este', 'Oeste', 'Zona rural', 'Prefiero no decir'], null, null],
        [2, 'single_choice', 'Rango de edad', true, ['18-25', '26-35', '36-50', '51-65', '66+', 'Prefiero no decir'], null, null],
        [3, 'scale_1_5', '¿Cómo califica la gestión municipal en general?', true, [
            ['value' => 1, 'label' => 'Muy mala'],
            ['value' => 2, 'label' => 'Mala'],
            ['value' => 3, 'label' => 'Regular'],
            ['value' => 4, 'label' => 'Buena'],
            ['value' => 5, 'label' => 'Muy buena'],
        ], null, null],
        [4, 'scale_1_5', 'Obras públicas e infraestructura', true, null, null, null],
        [5, 'scale_1_5', 'Seguridad ciudadana y serenazgo', true, null, null, null],
        [6, 'scale_1_5', 'Limpieza pública y recojo de residuos', true, null, null, null],
        [7, 'scale_1_5', 'Salud y programas sociales', true, null, null, null],
        [8, 'scale_1_5', 'Transporte, tránsito y señalización', true, null, null, null],
        [9, 'nps', '¿Qué tan probable es que recomiende los servicios municipales?', true, null, null, null],
        [10, 'multi_choice', '¿Qué debería priorizar la municipalidad?', true, [
            ['value' => 'obras_publicas', 'label' => 'Obras públicas'],
            ['value' => 'seguridad', 'label' => 'Seguridad'],
            ['value' => 'salud', 'label' => 'Salud'],
            ['value' => 'educacion', 'label' => 'Educación'],
            ['value' => 'transporte', 'label' => 'Transporte'],
            ['value' => 'medio_ambiente', 'label' => 'Medio ambiente'],
            ['value' => 'limpieza', 'label' => 'Limpieza'],
            ['value' => 'tributos', 'label' => 'Tributos'],
        ], 3, null],
        [11, 'open_text', '¿Qué nos quieres decir? Cuéntanos tu experiencia', false, null, null, 1000],
        [12, 'open_text', 'Si deseas respuesta, déjanos un correo o celular (opcional)', false, null, null, 120],
    ];

    app(TopicSeeder::class)->run();
    app(SurveySeeder::class)->run();
    app(TopicSeeder::class)->run();
    app(SurveySeeder::class)->run();

    $survey = Survey::query()->where('slug', 'percepcion-2026')->with('questions')->firstOrFail();
    $actualQuestions = $survey->questions->map(fn (SurveyQuestion $question): array => [
        $question->position,
        citizenSeederString($question->getRawOriginal('type')),
        $question->label,
        $question->is_required,
        $question->options,
        $question->max_selections,
        $question->max_length,
    ])->all();

    $actualTopicSlugs = Topic::query()->orderBy('slug')->get()->map(
        fn (Topic $topic): string => citizenSeederString($topic->getAttribute('slug')),
    )->all();

    expect(Topic::query()->count())->toBe(8)
        ->and($actualTopicSlugs)
        ->toBe(collect($expectedTopicSlugs)->sort()->values()->all())
        ->and(Survey::query()->count())->toBe(1)
        ->and($survey->title)->toBe('Encuesta de Percepción Ciudadana — San Ramón 2026')
        ->and(citizenSeederString($survey->getRawOriginal('status')))->toBe('published')
        ->and($survey->is_anonymous)->toBeTrue()
        ->and($survey->questions)->toHaveCount(12)
        ->and($actualQuestions)->toBe($expectedQuestions);
});
