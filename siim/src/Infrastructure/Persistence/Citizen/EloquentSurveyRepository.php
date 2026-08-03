<?php

declare(strict_types=1);

namespace SIIM\Infrastructure\Persistence\Citizen;

use App\Models\Survey as SurveyModel;
use App\Models\SurveyQuestion as SurveyQuestionModel;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;
use SIIM\Application\Citizen\Contracts\SurveyRepository;
use SIIM\Domain\Citizen\QuestionType;
use SIIM\Domain\Citizen\Survey;
use SIIM\Domain\Citizen\SurveyQuestion;
use SIIM\Domain\Citizen\SurveyStatus;
use UnexpectedValueException;

final class EloquentSurveyRepository implements SurveyRepository
{
    public function findBySlug(string $slug): ?Survey
    {
        $model = SurveyModel::query()->where('slug', $slug)->with('questions')->first();

        return $model === null ? null : $this->toDomain($model);
    }

    public function findPublishedBySlug(string $slug): ?Survey
    {
        $model = SurveyModel::query()
            ->where('slug', $slug)
            ->where('status', SurveyStatus::Published->value)
            ->with('questions')
            ->first();

        return $model === null ? null : $this->toDomain($model);
    }

    public function save(Survey $survey): void
    {
        DB::transaction(function () use ($survey): void {
            $model = SurveyModel::query()->updateOrCreate(
                ['id' => $survey->id],
                [
                    'slug' => $survey->slug,
                    'title' => $survey->title,
                    'description' => $survey->description,
                    'status' => $survey->status,
                    'opens_at' => $survey->opensAt,
                    'closes_at' => $survey->closesAt,
                    'is_anonymous' => $survey->isAnonymous,
                    'created_by' => $survey->createdBy,
                ],
            );

            $questionIds = [];

            foreach ($survey->questions as $question) {
                $questionIds[] = $question->id;
                SurveyQuestionModel::query()->updateOrCreate(
                    ['id' => $question->id],
                    [
                        'survey_id' => $model->id,
                        'position' => $question->position,
                        'type' => $question->type,
                        'label' => $question->label,
                        'help_text' => $question->helpText,
                        'is_required' => $question->isRequired,
                        'options' => $question->options === [] ? null : $question->options,
                        'max_selections' => $question->maxSelections,
                        'max_length' => $question->maxLength,
                        'topic_slug' => $question->topicSlug,
                    ],
                );
            }

            $staleQuestions = $model->questions();
            if ($questionIds !== []) {
                $staleQuestions->whereNotIn('id', $questionIds);
            }
            $staleQuestions->delete();
        });
    }

    private function toDomain(SurveyModel $model): Survey
    {
        $questions = $model->questions->map(
            fn (SurveyQuestionModel $question): SurveyQuestion => new SurveyQuestion(
                id: (string) $question->id,
                position: (int) $question->position,
                type: QuestionType::from($this->requiredString($question->getRawOriginal('type'))),
                label: (string) $question->label,
                isRequired: (bool) $question->is_required,
                options: is_array($question->options) ? $question->options : [],
                maxSelections: $question->max_selections === null ? null : (int) $question->max_selections,
                maxLength: $question->max_length === null ? null : (int) $question->max_length,
                helpText: $question->help_text === null ? null : (string) $question->help_text,
                topicSlug: $question->topic_slug === null ? null : (string) $question->topic_slug,
            ),
        )->all();

        return new Survey(
            id: (string) $model->id,
            slug: (string) $model->slug,
            title: (string) $model->title,
            status: SurveyStatus::from($this->requiredString($model->getRawOriginal('status'))),
            opensAt: $this->immutable($model->opens_at),
            closesAt: $this->immutable($model->closes_at),
            questions: $questions,
            description: $model->description === null ? null : (string) $model->description,
            isAnonymous: (bool) $model->is_anonymous,
            createdBy: $model->created_by === null ? null : (int) $model->created_by,
        );
    }

    private function immutable(mixed $value): ?DateTimeImmutable
    {
        if ($value === null) {
            return null;
        }

        if (! $value instanceof DateTimeInterface) {
            throw new UnexpectedValueException('Expected a date-time cast from the survey model.');
        }

        return DateTimeImmutable::createFromInterface($value);
    }

    private function requiredString(mixed $value): string
    {
        if (! is_string($value)) {
            throw new UnexpectedValueException('Expected a string attribute from the survey model.');
        }

        return $value;
    }
}
