<?php

declare(strict_types=1);

namespace SIIM\Infrastructure\Llm;

use SIIM\Application\Analysis\Contracts\FallbackSentimentAnalyzer;
use SIIM\Application\Analysis\Data\SentimentAnalysis;

final class LexiconSentimentAnalyzer implements FallbackSentimentAnalyzer
{
    /** Small, deliberately stable Spanish lexicon for offline resilience. */
    private const POSITIVE = ['bueno', 'buena', 'excelente', 'genial', 'feliz', 'rápido', 'rapido', 'mejor', 'eficiente', 'ayuda'];

    private const NEGATIVE = ['malo', 'mala', 'pésimo', 'pesimo', 'terrible', 'lento', 'inseguro', 'problema', 'problemas', 'queja'];

    private const NEGATIONS = ['no', 'nunca', 'sin'];

    public function analyze(string $text): SentimentAnalysis
    {
        preg_match_all('/[\p{L}\p{N}]+/u', mb_strtolower($text), $matches);
        $tokens = $matches[0];
        $value = 0;
        $hits = 0;
        foreach ($tokens as $index => $token) {
            $direction = in_array($token, self::POSITIVE, true) ? 1 : (in_array($token, self::NEGATIVE, true) ? -1 : 0);
            if ($direction === 0) {
                continue;
            }
            $hits++;
            $window = array_slice($tokens, max(0, $index - 3), min(3, $index));
            if (array_intersect($window, self::NEGATIONS) !== []) {
                $direction *= -1;
            }
            $value += $direction;
        }
        $score = max(-1.0, min(1.0, $value / max(1, $hits)));
        $polarity = $score > 0 ? 'positive' : ($score < 0 ? 'negative' : 'neutral');
        $confidence = min(1.0, $hits / 3);
        $reason = $hits === 0 ? 'Sin términos de sentimiento reconocidos.' : 'Clasificación léxica local determinista.';

        return new SentimentAnalysis($polarity, $score, $confidence, $reason, $this->provider(), $this->model());
    }

    public function provider(): string
    {
        return 'lexicon';
    }

    public function model(): string
    {
        return 'spanish-small-v1';
    }
}
