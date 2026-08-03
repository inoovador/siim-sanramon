<?php

declare(strict_types=1);

namespace SIIM\Application\Analysis\Contracts;

use SIIM\Application\Analysis\Data\SentimentAnalysis;

interface SentimentAnalyzer
{
    public function analyze(string $text): SentimentAnalysis;

    public function provider(): string;

    public function model(): string;
}
