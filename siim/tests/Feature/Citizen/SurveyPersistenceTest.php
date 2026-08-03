<?php

declare(strict_types=1);

use App\Models\AnalysisRun;
use App\Models\Comment;
use App\Models\SentimentScore;
use App\Models\Survey as SurveyModel;
use App\Models\SurveyAnswer as SurveyAnswerModel;
use App\Models\SurveyQuestion as SurveyQuestionModel;
use App\Models\SurveyResponse as SurveyResponseModel;
use App\Models\Topic;
use App\Models\TopicAssignment;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use SIIM\Application\Citizen\Contracts\SurveyRepository;
use SIIM\Domain\Citizen\QuestionType;
use SIIM\Domain\Citizen\Survey;
use SIIM\Domain\Citizen\SurveyQuestion;
use SIIM\Domain\Citizen\SurveyStatus;
use SIIM\Infrastructure\Persistence\Citizen\EloquentSurveyRepository;

uses(RefreshDatabase::class);

it('binds the survey repository and round trips a published survey with ordered questions', function (): void {
    $repository = app(SurveyRepository::class);

    expect($repository)->toBeInstanceOf(EloquentSurveyRepository::class);

    $surveyId = (string) Str::uuid();
    $firstQuestionId = (string) Str::uuid();
    $secondQuestionId = (string) Str::uuid();
    $survey = new Survey(
        id: $surveyId,
        slug: 'repositorio-ciudadano',
        title: 'Encuesta del repositorio',
        status: SurveyStatus::Published,
        opensAt: new DateTimeImmutable('2026-01-01 00:00:00'),
        closesAt: new DateTimeImmutable('2026-12-31 23:59:59'),
        questions: [
            new SurveyQuestion(
                id: $secondQuestionId,
                position: 2,
                type: QuestionType::OpenText,
                label: 'Comentario',
                isRequired: false,
                maxLength: 120,
            ),
            new SurveyQuestion(
                id: $firstQuestionId,
                position: 1,
                type: QuestionType::SingleChoice,
                label: 'Zona',
                options: ['Centro', 'Norte'],
            ),
        ],
        description: 'Descripción persistida',
        isAnonymous: true,
    );

    $repository->save($survey);
    $restored = $repository->findPublishedBySlug('repositorio-ciudadano');

    expect($restored)->not->toBeNull()
        ->and($restored?->id)->toBe($surveyId)
        ->and($restored?->status)->toBe(SurveyStatus::Published)
        ->and($restored?->opensAt?->format('Y-m-d H:i:s'))->toBe('2026-01-01 00:00:00')
        ->and($restored?->description)->toBe('Descripción persistida')
        ->and(array_map(fn (SurveyQuestion $question): int => $question->position, $restored?->questions ?? []))
        ->toBe([1, 2])
        ->and($restored?->questions[0]->type)->toBe(QuestionType::SingleChoice)
        ->and($restored?->questions[0]->options)->toBe(['Centro', 'Norte'])
        ->and($restored?->questions[1]->maxLength)->toBe(120);
});

it('does not return a draft survey through the published lookup', function (): void {
    SurveyModel::factory()->draft()->create(['slug' => 'borrador']);

    expect(app(SurveyRepository::class)->findPublishedBySlug('borrador'))->toBeNull();
});

it('provides typed model casts and aggregate relationships with valid factories', function (): void {
    $survey = SurveyModel::factory()->published()->create();
    $question = SurveyQuestionModel::factory()->for($survey)->create();
    $comment = Comment::factory()->webSurvey()->create();
    $response = SurveyResponseModel::factory()->for($survey)->for($comment)->create();
    $answer = SurveyAnswerModel::factory()->for($response, 'response')->for($question, 'question')->create();
    $sentiment = SentimentScore::factory()->for($comment)->create();
    $topic = Topic::factory()->create();
    $assignment = TopicAssignment::factory()->for($comment)->for($topic)->create();
    $run = AnalysisRun::factory()->for($comment)->create();

    expect($survey->status)->toBe(SurveyStatus::Published)
        ->and($question->type)->toBe(QuestionType::Scale1To5)
        ->and($survey->questions->sole()->is($question))->toBeTrue()
        ->and($response->survey?->is($survey))->toBeTrue()
        ->and($response->comment?->is($comment))->toBeTrue()
        ->and($response->answers->sole()->is($answer))->toBeTrue()
        ->and($comment->sentimentScore?->is($sentiment))->toBeTrue()
        ->and($comment->topics->sole()->is($topic))->toBeTrue()
        ->and($assignment->confidence)->toBeFloat()
        ->and($run->cost_usd)->toBeString();
});

it('enforces one response per survey ip hash and response date', function (): void {
    $survey = SurveyModel::factory()->create();
    $attributes = [
        'survey_id' => $survey->id,
        'ip_hash' => str_repeat('f', 64),
        'response_date' => '2026-07-10',
    ];

    SurveyResponseModel::factory()->create($attributes);

    expect(fn () => SurveyResponseModel::factory()->create($attributes))
        ->toThrow(QueryException::class);
});
