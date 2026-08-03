<?php

declare(strict_types=1);

namespace SIIM\Infrastructure\Llm;

use Closure;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use JsonException;
use SIIM\Application\Analysis\Contracts\PrimarySentimentAnalyzer;
use SIIM\Application\Analysis\Data\SentimentAnalysis;
use SIIM\Application\Analysis\Data\TokenUsage;
use SIIM\Application\Analysis\Exceptions\SentimentAnalysisException;

final readonly class NvidiaSentimentAnalyzer implements PrimarySentimentAnalyzer
{
    /** @var list<int> */
    public const RETRY_DELAYS_SECONDS = [10, 60, 300];

    // Every retry reserves the next 20-second HTTP attempt and never exceeds the queued job's 90-second limit.

    public function __construct(
        private string $apiKey,
        private string $baseUrl,
        private string $model,
        private int $timeout = 20,
        private bool $verifySsl = true,
        private ?Closure $sleeper = null,
        private int $analysisTimeBudgetSeconds = 75,
    ) {}

    public function analyze(string $text): SentimentAnalysis
    {
        if ($this->apiKey === '') {
            throw new SentimentAnalysisException('NVIDIA sentiment analysis is not configured.');
        }
        $remainingBudget = $this->analysisTimeBudgetSeconds;
        $response = null;

        foreach ([...self::RETRY_DELAYS_SECONDS, null] as $delay) {
            if ($remainingBudget < $this->timeout) {
                throw new SentimentAnalysisException('NVIDIA retry budget exhausted.');
            }
            try {
                $remainingBudget -= $this->timeout;
                $response = Http::withToken($this->apiKey)->timeout($this->timeout)->acceptJson()->asJson()
                    ->withOptions(['verify' => $this->verifySsl])
                    ->post(rtrim($this->baseUrl, '/') . '/chat/completions', [
                        'model' => $this->model,
                        'messages' => [['role' => 'system', 'content' => 'Responde solo JSON estricto: {"polarity":"positive|neutral|negative","score":-1..1,"confidence":0..1,"reason":"..."}.'], ['role' => 'user', 'content' => $text]],
                        'temperature' => 0, 'max_tokens' => 180, 'stream' => false, 'response_format' => ['type' => 'json_object'],
                    ]);
            } catch (ConnectionException $exception) {
                if ($delay === null) {
                    throw new SentimentAnalysisException('NVIDIA sentiment request failed.', previous: $exception);
                }
                $this->sleepIfWithinBudget($delay, $remainingBudget);
                $remainingBudget -= $delay;

                continue;
            }
            if (! $response->failed()) {
                break;
            }
            if (! ($response->status() === 429 || $response->serverError()) || $delay === null) {
                throw new SentimentAnalysisException('NVIDIA sentiment request was rejected.');
            }
            $this->sleepIfWithinBudget($delay, $remainingBudget);
            $remainingBudget -= $delay;
        }
        if ($response === null || $response->failed()) {
            throw new SentimentAnalysisException('NVIDIA sentiment request was rejected.');
        }
        try {
            $payload = $response->json();
        } catch (JsonException $exception) {
            throw new SentimentAnalysisException('NVIDIA sentiment response was invalid.', previous: $exception);
        }
        if (! is_array($payload)) {
            throw new SentimentAnalysisException('NVIDIA sentiment response was invalid.');
        }
        $content = $this->contentFromPayload($payload);
        try {
            $json = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new SentimentAnalysisException('NVIDIA sentiment response was invalid.', previous: $exception);
        }
        $sentiment = $this->strictSentiment($json);
        $usage = is_array($payload['usage'] ?? null) ? $payload['usage'] : [];

        return new SentimentAnalysis($sentiment['polarity'], $sentiment['score'], $sentiment['confidence'], $sentiment['reason'], $this->provider(), is_string($payload['model'] ?? null) ? $payload['model'] : $this->model, new TokenUsage($this->tokenCount($usage['prompt_tokens'] ?? null), $this->tokenCount($usage['completion_tokens'] ?? null)));
    }

    public function provider(): string
    {
        return 'nvidia';
    }

    public function model(): string
    {
        return $this->model;
    }

    private function sleepIfWithinBudget(int $seconds, int $remainingBudget): void
    {
        if ($seconds + $this->timeout > $remainingBudget) {
            throw new SentimentAnalysisException('NVIDIA retry budget exhausted.');
        }
        $this->sleep($seconds);
    }

    private function sleep(int $seconds): void
    {
        if ($this->sleeper !== null) {
            ($this->sleeper)($seconds);

            return;
        }
        sleep($seconds);
    }

    /** @param array<mixed> $payload */
    private function contentFromPayload(array $payload): string
    {
        $choices = $payload['choices'] ?? null;
        $choice = is_array($choices) ? ($choices[0] ?? null) : null;
        $message = is_array($choice) ? ($choice['message'] ?? null) : null;
        $content = is_array($message) ? ($message['content'] ?? null) : null;
        if (! is_string($content) || trim($content) === '') {
            throw new SentimentAnalysisException('NVIDIA sentiment response was empty or malformed.');
        }

        return $content;
    }

    /** @return array{polarity: string, score: float, confidence: float, reason: string} */
    private function strictSentiment(mixed $json): array
    {
        if (! is_array($json)) {
            throw new SentimentAnalysisException('NVIDIA sentiment response did not match the schema.');
        }
        $keys = array_keys($json);
        sort($keys);
        if ($keys !== ['confidence', 'polarity', 'reason', 'score']) {
            throw new SentimentAnalysisException('NVIDIA sentiment response did not match the schema.');
        }
        $isValid = is_string($json['polarity'])
            && in_array($json['polarity'], ['positive', 'neutral', 'negative'], true)
            && $this->isFiniteNumber($json['score'])
            && $this->isFiniteNumber($json['confidence'])
            && (float) $json['score'] >= -1
            && (float) $json['score'] <= 1
            && (float) $json['confidence'] >= 0
            && (float) $json['confidence'] <= 1
            && is_string($json['reason'])
            && trim($json['reason']) !== ''
            && mb_strlen($json['reason']) <= 500;
        if (! $isValid) {
            throw new SentimentAnalysisException('NVIDIA sentiment response did not match the schema.');
        }

        return [
            'polarity' => $json['polarity'],
            'score' => (float) $json['score'],
            'confidence' => (float) $json['confidence'],
            'reason' => $json['reason'],
        ];
    }

    private function isFiniteNumber(mixed $value): bool
    {
        return (is_int($value) || is_float($value)) && is_finite((float) $value);
    }

    private function tokenCount(mixed $value): int
    {
        return is_int($value) && $value >= 0 ? $value : 0;
    }
}
