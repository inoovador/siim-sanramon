<?php

declare(strict_types=1);

namespace SIIM\Infrastructure\Persistence\Analysis;

use App\Models\AnalysisRun;
use App\Models\Comment;
use App\Models\SentimentScore;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use SIIM\Application\Analysis\Contracts\AnalysisRepository;
use SIIM\Application\Analysis\Data\AnalysisPersistence;
use SIIM\Application\Analysis\Data\AnalyzableComment;

final class EloquentAnalysisRepository implements AnalysisRepository
{
    public function findComment(string $commentId): ?AnalyzableComment
    {
        $comment = Comment::query()->find($commentId);

        return $comment === null ? null : new AnalyzableComment((string) $comment->id, (string) $comment->text);
    }

    public function todayCost(DateTimeImmutable $now): float
    {
        return (float) AnalysisRun::query()->whereDate('requested_at', $now->format('Y-m-d'))->sum('cost_usd');
    }

    public function persist(AnalysisPersistence $persistence): void
    {
        DB::transaction(function () use ($persistence): void {
            $sentiment = $persistence->sentiment;
            SentimentScore::query()->updateOrCreate(['comment_id' => $persistence->comment->id], [
                'polarity' => $sentiment->polarity, 'score' => $sentiment->score, 'confidence' => $sentiment->confidence,
                'reason' => mb_substr($sentiment->reason, 0, 500), 'analyzed_at' => $persistence->completedAt,
            ]);
            AnalysisRun::query()->create([
                'comment_id' => $persistence->comment->id, 'llm_provider' => $sentiment->provider, 'llm_model' => $sentiment->model,
                'status' => $persistence->status, 'tokens_input' => $sentiment->usage->inputTokens, 'tokens_output' => $sentiment->usage->outputTokens,
                'cost_usd' => $sentiment->costUsd, 'error_message' => $persistence->errorMessage === null ? null : mb_substr($persistence->errorMessage, 0, 500),
                'requested_at' => $persistence->requestedAt, 'completed_at' => $persistence->completedAt,
            ]);
        });
    }
}
