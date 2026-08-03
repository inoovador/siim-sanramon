<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Survey;
use App\Models\SurveyQuestion;
use Illuminate\Database\Seeder;
use SIIM\Domain\Citizen\QuestionType;
use SIIM\Domain\Citizen\SurveyStatus;

class SurveySeeder extends Seeder
{
    public function run(): void
    {
        $survey = Survey::query()->updateOrCreate(
            ['slug' => 'percepcion-2026'],
            [
                'title' => 'Encuesta de Percepción Ciudadana — San Ramón 2026',
                'description' => null,
                'status' => SurveyStatus::Published,
                'opens_at' => null,
                'closes_at' => null,
                'is_anonymous' => true,
                'created_by' => null,
            ],
        );

        foreach ($this->questions() as $question) {
            SurveyQuestion::query()->updateOrCreate(
                ['survey_id' => $survey->id, 'position' => $question['position']],
                $question,
            );
        }

        $survey->questions()->whereNotIn('position', range(1, 12))->delete();
    }

    /** @return list<array<string, mixed>> */
    private function questions(): array
    {
        return [
            $this->question(1, QuestionType::SingleChoice, '¿En qué zona del distrito vives?', options: [
                'Centro', 'Norte', 'Sur', 'Este', 'Oeste', 'Zona rural', 'Prefiero no decir',
            ]),
            $this->question(2, QuestionType::SingleChoice, 'Rango de edad', options: [
                '18-25', '26-35', '36-50', '51-65', '66+', 'Prefiero no decir',
            ]),
            $this->question(3, QuestionType::Scale1To5, '¿Cómo califica la gestión municipal en general?', options: [
                ['value' => 1, 'label' => 'Muy mala'],
                ['value' => 2, 'label' => 'Mala'],
                ['value' => 3, 'label' => 'Regular'],
                ['value' => 4, 'label' => 'Buena'],
                ['value' => 5, 'label' => 'Muy buena'],
            ]),
            $this->question(4, QuestionType::Scale1To5, 'Obras públicas e infraestructura'),
            $this->question(5, QuestionType::Scale1To5, 'Seguridad ciudadana y serenazgo'),
            $this->question(6, QuestionType::Scale1To5, 'Limpieza pública y recojo de residuos'),
            $this->question(7, QuestionType::Scale1To5, 'Salud y programas sociales'),
            $this->question(8, QuestionType::Scale1To5, 'Transporte, tránsito y señalización'),
            $this->question(9, QuestionType::Nps, '¿Qué tan probable es que recomiende los servicios municipales?'),
            $this->question(10, QuestionType::MultiChoice, '¿Qué debería priorizar la municipalidad?', options: [
                ['value' => 'obras_publicas', 'label' => 'Obras públicas'],
                ['value' => 'seguridad', 'label' => 'Seguridad'],
                ['value' => 'salud', 'label' => 'Salud'],
                ['value' => 'educacion', 'label' => 'Educación'],
                ['value' => 'transporte', 'label' => 'Transporte'],
                ['value' => 'medio_ambiente', 'label' => 'Medio ambiente'],
                ['value' => 'limpieza', 'label' => 'Limpieza'],
                ['value' => 'tributos', 'label' => 'Tributos'],
            ], maxSelections: 3),
            $this->question(
                11,
                QuestionType::OpenText,
                '¿Qué nos quieres decir? Cuéntanos tu experiencia',
                isRequired: false,
                maxLength: 1000,
            ),
            $this->question(
                12,
                QuestionType::OpenText,
                'Si deseas respuesta, déjanos un correo o celular (opcional)',
                isRequired: false,
                maxLength: 120,
            ),
        ];
    }

    /**
     * @param  array<mixed>|null  $options
     * @return array<string, mixed>
     */
    private function question(
        int $position,
        QuestionType $type,
        string $label,
        bool $isRequired = true,
        ?array $options = null,
        ?int $maxSelections = null,
        ?int $maxLength = null,
    ): array {
        return [
            'position' => $position,
            'type' => $type,
            'label' => $label,
            'help_text' => null,
            'is_required' => $isRequired,
            'options' => $options,
            'max_selections' => $maxSelections,
            'max_length' => $maxLength,
            'topic_slug' => null,
        ];
    }
}
