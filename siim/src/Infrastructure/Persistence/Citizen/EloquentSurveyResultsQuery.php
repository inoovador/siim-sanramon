<?php

declare(strict_types=1);

namespace SIIM\Infrastructure\Persistence\Citizen;

use App\Models\Survey;
use App\Models\SurveyAnswer;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use SIIM\Application\Citizen\Queries\SurveyResultsQuery;
use SIIM\Application\Citizen\ReadModels\EncryptedSurveyContact;
use SIIM\Application\Citizen\ReadModels\PriorityOptionResult;
use SIIM\Application\Citizen\ReadModels\ScaleDistribution;
use SIIM\Application\Citizen\ReadModels\SurveyComment;
use SIIM\Application\Citizen\ReadModels\SurveyCommentsPage;
use SIIM\Application\Citizen\ReadModels\SurveyExportDefinition;
use SIIM\Application\Citizen\ReadModels\SurveyExportRow;
use SIIM\Application\Citizen\ReadModels\SurveyFilterOptions;
use SIIM\Application\Citizen\ReadModels\SurveyFilters;
use SIIM\Application\Citizen\ReadModels\SurveyOverview;
use SIIM\Application\Citizen\ReadModels\SurveyResults;
use SIIM\Domain\Citizen\QuestionType;
use SIIM\Domain\Citizen\SurveyStatus;
use UnexpectedValueException;

final class EloquentSurveyResultsQuery implements SurveyResultsQuery
{
    public function surveys(): array
    {
        return Survey::query()
            ->withCount('responses')
            ->withMax('responses', 'submitted_at')
            ->withCount('attempts')
            ->withCount(['attempts as completed_attempts_count' => static fn (Builder $query): Builder => $query
                ->whereNotNull('response_id')
                ->whereNotNull('completed_at')])
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Survey $survey): SurveyOverview => $this->overview($survey))
            ->all();
    }

    public function activeSurvey(DateTimeImmutable $at): ?SurveyOverview
    {
        $survey = Survey::query()
            ->where('status', SurveyStatus::Published->value)
            ->where(static function (Builder $query) use ($at): void {
                $query->whereNull('opens_at')->orWhere('opens_at', '<=', $at);
            })
            ->where(static function (Builder $query) use ($at): void {
                $query->whereNull('closes_at')->orWhere('closes_at', '>=', $at);
            })
            ->withCount('responses')
            ->withMax('responses', 'submitted_at')
            ->withCount('attempts')
            ->withCount(['attempts as completed_attempts_count' => static fn (Builder $query): Builder => $query
                ->whereNotNull('response_id')
                ->whereNotNull('completed_at')])
            ->orderByDesc('opens_at')
            ->first();

        return $survey === null ? null : $this->overview($survey);
    }

    public function filterOptions(string $slug): ?SurveyFilterOptions
    {
        $survey = Survey::query()->where('slug', $slug)->with('questions')->first();
        if ($survey === null) {
            return null;
        }

        $zone = $survey->questions->firstWhere('position', 1);
        $age = $survey->questions->firstWhere('position', 2);

        return new SurveyFilterOptions(
            slug: (string) $survey->slug,
            title: (string) $survey->title,
            status: $this->status($survey),
            zones: $this->optionValues($zone),
            ageRanges: $this->optionValues($age),
        );
    }

    public function results(string $slug, SurveyFilters $filters): ?SurveyResults
    {
        $survey = Survey::query()->where('slug', $slug)->with('questions')->first();
        if ($survey === null) {
            return null;
        }

        $responseIds = $this->responses($survey->id, $filters)->select('survey_responses.id');
        $total = (clone $responseIds)->count();
        $general = $survey->questions->firstWhere('position', 3);
        $npsQuestion = $survey->questions->firstWhere('position', 9);
        $priorityQuestion = $survey->questions
            ->first(static fn (SurveyQuestion $question): bool => $question->type === QuestionType::MultiChoice);

        $generalAverage = $general === null ? null : $this->average($general->id, clone $responseIds);
        $nps = $npsQuestion === null ? null : $this->nps($npsQuestion->id, clone $responseIds);

        $scales = $survey->questions
            ->filter(static fn (SurveyQuestion $question): bool => $question->type === QuestionType::Scale1To5)
            ->map(fn (SurveyQuestion $question): ScaleDistribution => $this->scale($question, clone $responseIds))
            ->values()
            ->all();

        return new SurveyResults(
            slug: (string) $survey->slug,
            title: (string) $survey->title,
            status: $this->status($survey),
            totalResponses: $total,
            generalAverage: $generalAverage,
            nps: $nps,
            positiveSentimentPercent: $this->positiveSentiment(clone $responseIds),
            scales: $scales,
            priorities: $priorityQuestion === null ? [] : $this->priorities($priorityQuestion, clone $responseIds),
            comments: $this->comments($survey->id, $filters),
        );
    }

    public function exportDefinition(string $slug): ?SurveyExportDefinition
    {
        $survey = Survey::query()->where('slug', $slug)->with('questions')->first();
        if ($survey === null) {
            return null;
        }

        $headers = ['response_id', 'submitted_at'];
        foreach ($this->exportQuestions($survey->questions) as $question) {
            $headers[] = (string) $question->label;
        }
        $headers[] = 'contact_provided';

        return new SurveyExportDefinition((string) $survey->slug, $headers);
    }

    public function exportRows(string $slug, SurveyFilters $filters): iterable
    {
        $survey = Survey::query()->where('slug', $slug)->with('questions')->first();
        if ($survey === null) {
            return;
        }

        $questions = $this->exportQuestions($survey->questions);
        $responses = $this->responses($survey->id, $filters)
            ->with('answers')
            ->orderBy('survey_responses.id')
            ->lazy();

        foreach ($responses as $response) {
            $answers = $response->answers->keyBy('question_id');
            $cells = [(string) $response->id, $this->dateTime($response->submitted_at)->format('Y-m-d H:i:s')];
            foreach ($questions as $question) {
                $answer = $answers->get($question->id);
                $cells[] = $answer === null ? '' : $this->exportValue($question, $answer);
            }
            $cells[] = $response->respondent_contact === null ? 'no' : 'sí';
            yield new SurveyExportRow($cells);
        }
    }

    public function encryptedContact(string $slug, string $responseId): ?EncryptedSurveyContact
    {
        $response = SurveyResponse::query()
            ->where('id', $responseId)
            ->whereHas('survey', static fn (Builder $query): Builder => $query->where('slug', $slug))
            ->whereNotNull('respondent_contact')
            ->first();

        if ($response === null || ! is_string($response->respondent_contact)) {
            return null;
        }

        return new EncryptedSurveyContact(
            (string) $response->survey_id,
            (string) $response->id,
            $response->respondent_contact,
        );
    }

    /** @return Builder<SurveyResponse> */
    private function responses(string $surveyId, SurveyFilters $filters): Builder
    {
        $query = SurveyResponse::query()->where('survey_id', $surveyId);
        if ($filters->from !== null) {
            $query->where('submitted_at', '>=', $filters->from->setTime(0, 0));
        }
        if ($filters->to !== null) {
            $query->where('submitted_at', '<', $filters->to->modify('+1 day')->setTime(0, 0));
        }
        if ($filters->zone !== null) {
            $query->where('zone', $filters->zone);
        }
        if ($filters->ageRange !== null) {
            $query->where('age_range', $filters->ageRange);
        }

        return $query;
    }

    /** @param Builder<SurveyResponse> $responseIds */
    private function average(string $questionId, Builder $responseIds): ?float
    {
        $average = SurveyAnswer::query()
            ->where('question_id', $questionId)
            ->whereIn('response_id', $responseIds)
            ->avg('value_int');

        return $average === null ? null : round((float) $average, 2);
    }

    /** @param Builder<SurveyResponse> $responseIds */
    private function nps(string $questionId, Builder $responseIds): ?float
    {
        /** @var array<int, int|string> $values */
        $values = SurveyAnswer::query()
            ->where('question_id', $questionId)
            ->whereIn('response_id', $responseIds)
            ->whereNotNull('value_int')
            ->pluck('value_int')
            ->all();
        if ($values === []) {
            return null;
        }

        $promoters = count(array_filter($values, static fn (int|string $value): bool => (int) $value >= 9));
        $detractors = count(array_filter($values, static fn (int|string $value): bool => (int) $value <= 6));

        return round(100 * ($promoters - $detractors) / count($values), 2);
    }

    /** @param Builder<SurveyResponse> $responseIds */
    private function scale(SurveyQuestion $question, Builder $responseIds): ScaleDistribution
    {
        /** @var array<int, int> $counts */
        $counts = SurveyAnswer::query()
            ->where('question_id', $question->id)
            ->whereIn('response_id', $responseIds)
            ->whereBetween('value_int', [1, 5])
            ->selectRaw('value_int, COUNT(*) as aggregate')
            ->groupBy('value_int')
            ->pluck('aggregate', 'value_int')
            ->map(fn (mixed $count): int => $this->integer($count))
            ->all();
        $buckets = [];
        $sum = 0;
        $answers = 0;
        foreach (range(1, 5) as $value) {
            $count = $counts[$value] ?? 0;
            $buckets[$value] = $count;
            $sum += $value * $count;
            $answers += $count;
        }

        return new ScaleDistribution(
            (string) $question->id,
            (int) $question->position,
            (string) $question->label,
            $buckets,
            $answers === 0 ? null : round($sum / $answers, 2),
            $answers,
        );
    }

    /**
     * @param  Builder<SurveyResponse>  $responseIds
     * @return list<PriorityOptionResult>
     */
    private function priorities(SurveyQuestion $question, Builder $responseIds): array
    {
        $counts = [];
        $answered = 0;
        $answers = SurveyAnswer::query()
            ->where('question_id', $question->id)
            ->whereIn('response_id', $responseIds)
            ->get(['value_json']);
        foreach ($answers as $answer) {
            if (! is_array($answer->value_json) || $answer->value_json === []) {
                continue;
            }
            $answered++;
            foreach ($answer->value_json as $value) {
                if (is_string($value)) {
                    $counts[$value] = ($counts[$value] ?? 0) + 1;
                }
            }
        }

        $results = [];
        foreach ($this->normalizedOptions($question) as $option) {
            $count = $counts[$option['value']] ?? 0;
            $results[] = new PriorityOptionResult(
                $option['value'],
                $option['label'],
                $count,
                $answered === 0 ? 0.0 : round(100 * $count / $answered, 2),
            );
        }
        usort($results, static fn (PriorityOptionResult $left, PriorityOptionResult $right): int => $right->count <=> $left->count);

        return $results;
    }

    /** @param Builder<SurveyResponse> $responseIds */
    private function positiveSentiment(Builder $responseIds): ?float
    {
        $query = SurveyResponse::query()
            ->whereIn('survey_responses.id', $responseIds)
            ->join('sentiment_scores', 'sentiment_scores.comment_id', '=', 'survey_responses.comment_id');
        $analyzed = (clone $query)->count();
        if ($analyzed === 0) {
            return null;
        }

        return round(100 * (clone $query)->where('sentiment_scores.polarity', 'positive')->count() / $analyzed, 2);
    }

    private function comments(string $surveyId, SurveyFilters $filters): SurveyCommentsPage
    {
        $query = $this->responses($surveyId, $filters)
            ->join('comments', 'comments.id', '=', 'survey_responses.comment_id')
            ->leftJoin('sentiment_scores', 'sentiment_scores.comment_id', '=', 'comments.id');
        if ($filters->search !== null && trim($filters->search) !== '') {
            $escaped = str_replace(
                ['!', '%', '_'],
                ['!!', '!%', '!_'],
                trim($filters->search),
            );
            $query->whereRaw("comments.text like ? escape '!'", ["%{$escaped}%"]);
        }

        $total = (clone $query)->count();
        $lastPage = max(1, (int) ceil($total / $filters->perPage));
        $page = min(max(1, $filters->page), $lastPage);
        $rows = $query
            ->orderByDesc('survey_responses.submitted_at')
            ->orderByDesc('survey_responses.id')
            ->offset(($page - 1) * $filters->perPage)
            ->limit($filters->perPage)
            ->get([
                'survey_responses.id as response_id',
                'survey_responses.submitted_at',
                'survey_responses.respondent_contact',
                'comments.text',
                'sentiment_scores.polarity',
            ]);

        $items = $rows->map(fn (SurveyResponse $row): SurveyComment => new SurveyComment(
            responseId: $this->requiredString($row->getAttribute('response_id')),
            text: $this->requiredString($row->getAttribute('text')),
            polarity: is_string($row->getAttribute('polarity')) ? $row->getAttribute('polarity') : null,
            submittedAt: $this->dateTime($row->getAttribute('submitted_at')),
            contactProvided: $row->getAttribute('respondent_contact') !== null,
        ))->all();

        return new SurveyCommentsPage($items, $page, $lastPage, $total);
    }

    private function overview(Survey $survey): SurveyOverview
    {
        $attempts = $this->integer($survey->getAttribute('attempts_count'));
        $completed = $this->integer($survey->getAttribute('completed_attempts_count'));
        $last = $survey->getAttribute('responses_max_submitted_at');

        return new SurveyOverview(
            id: (string) $survey->id,
            slug: (string) $survey->slug,
            title: (string) $survey->title,
            status: $this->status($survey),
            responseCount: $this->integer($survey->getAttribute('responses_count')),
            lastResponseAt: $last === null ? null : $this->dateTime($last),
            completionRate: $attempts === 0 ? null : round(100 * $completed / $attempts, 2),
        );
    }

    private function status(Survey $survey): string
    {
        return $survey->status instanceof SurveyStatus ? $survey->status->value : (string) $survey->status;
    }

    /** @return list<string> */
    private function optionValues(?SurveyQuestion $question): array
    {
        return $question === null ? [] : array_column($this->normalizedOptions($question), 'value');
    }

    /** @return list<array{value: string, label: string}> */
    private function normalizedOptions(SurveyQuestion $question): array
    {
        $options = is_array($question->options) ? $question->options : [];

        return array_values(array_map(static function (string|array $option): array {
            if (is_string($option)) {
                return ['value' => $option, 'label' => $option];
            }

            return [
                'value' => (string) $option['value'],
                'label' => isset($option['label']) ? (string) $option['label'] : (string) $option['value'],
            ];
        }, $options));
    }

    /**
     * @param  Collection<int, SurveyQuestion>  $questions
     * @return list<SurveyQuestion>
     */
    private function exportQuestions(Collection $questions): array
    {
        return $questions
            ->reject(static fn (SurveyQuestion $question): bool => (int) $question->position === 12)
            ->sortBy('position')
            ->values()
            ->all();
    }

    private function exportValue(SurveyQuestion $question, SurveyAnswer $answer): string
    {
        if ($answer->value_int !== null) {
            return (string) $answer->value_int;
        }
        if ($answer->value_text !== null) {
            return (string) $answer->value_text;
        }
        if (! is_array($answer->value_json)) {
            return '';
        }

        $selected = array_map('strval', $answer->value_json);
        $labels = [];
        foreach ($this->normalizedOptions($question) as $option) {
            if (in_array($option['value'], $selected, true)) {
                $labels[] = $option['label'];
            }
        }

        return implode(' | ', $labels);
    }

    private function dateTime(mixed $value): DateTimeImmutable
    {
        if ($value instanceof DateTimeInterface) {
            return DateTimeImmutable::createFromInterface($value);
        }
        if (is_string($value)) {
            return new DateTimeImmutable($value);
        }

        throw new UnexpectedValueException('Expected date-time value.');
    }

    private function integer(mixed $value): int
    {
        if (! is_int($value) && ! is_numeric($value)) {
            throw new UnexpectedValueException('Expected integer value.');
        }

        return (int) $value;
    }

    private function requiredString(mixed $value): string
    {
        if (! is_string($value)) {
            throw new UnexpectedValueException('Expected string value.');
        }

        return $value;
    }
}
