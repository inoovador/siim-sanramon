<?php

declare(strict_types=1);

namespace SIIM\Infrastructure\Persistence\Analysis;

use App\Models\AnalysisRun;
use App\Models\Comment;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
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

    public function isComplete(string $commentId): bool
    {
        return AnalysisRun::query()
            ->where('comment_id', $commentId)
            ->whereIn('status', ['success', 'fallback'])
            ->exists();
    }

    public function persist(AnalysisPersistence $persistence): void
    {
        DB::transaction(function () use ($persistence): void {
            if ($persistence->updateScore) {
                $sentiment = $persistence->score;
                DB::table('sentiment_scores')->upsert([[
                    'comment_id' => $persistence->comment->id,
                    'polarity' => $sentiment->polarity,
                    'score' => $sentiment->score,
                    'confidence' => $sentiment->confidence,
                    'reason' => mb_substr($sentiment->reason, 0, 500),
                    'analyzed_at' => $persistence->completedAt,
                ]], ['comment_id'], ['polarity', 'score', 'confidence', 'reason', 'analyzed_at']);
            }

            DB::table('analysis_runs')->insert([
                'id' => (string) Str::uuid(),
                'comment_id' => $persistence->comment->id,
                'llm_provider' => $persistence->provider,
                'llm_model' => $persistence->model,
                'status' => $persistence->status,
                'fallback_used' => $persistence->fallbackUsed,
                'tokens_input' => $persistence->usage->inputTokens,
                'tokens_output' => $persistence->usage->outputTokens,
                'cost_usd' => $persistence->costUsd,
                'error_category' => $persistence->errorCategory?->value,
                'error_message' => $persistence->errorMessage === null ? null : mb_substr($persistence->errorMessage, 0, 500),
                'requested_at' => $persistence->requestedAt,
                'completed_at' => $persistence->completedAt,
            ]);
        });
    }
}
