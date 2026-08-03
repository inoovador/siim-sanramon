<?php

declare(strict_types=1);

namespace SIIM\Application\Analysis\Contracts;

use DateTimeImmutable;
use SIIM\Application\Analysis\Data\AnalysisPersistence;
use SIIM\Application\Analysis\Data\AnalyzableComment;

interface AnalysisRepository
{
    public function findComment(string $commentId): ?AnalyzableComment;

    public function todayCost(DateTimeImmutable $now): float;

    public function isComplete(string $commentId): bool;

    public function persist(AnalysisPersistence $persistence): void;
}
