<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Jobs\AnalyzeCommentJob;
use SIIM\Domain\Citizen\Events\CommentIngested;

final class DispatchAnalysisOnCommentIngested
{
    public function handle(CommentIngested $event): void
    {
        AnalyzeCommentJob::dispatch($event->commentId)->onQueue('analysis')->afterCommit();
    }
}
