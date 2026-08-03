<?php

declare(strict_types=1);

namespace SIIM\Infrastructure\Llm;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use JsonException;
use SIIM\Application\Analysis\Contracts\PrimarySentimentAnalyzer;
use SIIM\Application\Analysis\Data\AnalysisErrorCategory;
use SIIM\Application\Analysis\Data\SentimentAnalysis;
use SIIM\Application\Analysis\Data\TokenUsage;
use SIIM\Application\Analysis\Exceptions\RetryableSentimentAnalysisException;
use SIIM\Application\Analysis\Exceptions\TerminalSentimentAnalysisException;

final readonly class NvidiaSentimentAnalyzer implements PrimarySentimentAnalyzer
{
    private const MAX_TOKENS = 180;

    public const REQUEST_TIMEOUT_SECONDS = 20;

    public function __construct(
        private string $apiKey,
        private string $baseUrl,
        private string $model,
        private bool $verifySsl = true,
        string $environment = 'production',
    ) {
        NvidiaTransportPolicy::assertSafe($baseUrl, $verifySsl, $environment);
    }

    public function analyze(string $text): SentimentAnalysis
    {
        if ($this->apiKey === '') {
            throw new TerminalSentimentAnalysisException(AnalysisErrorCategory::NotConfigured, 'Primary sentiment analysis is not configured.');
        }

        try {
            $response = $this->request($text);
        } catch (ConnectionException) {
            throw new RetryableSentimentAnalysisException(AnalysisErrorCategory::ConnectionFailure, 'Provider connection failed.');
        }

        if ($response->status() === 429) {
            throw new RetryableSentimentAnalysisException(AnalysisErrorCategory::RateLimited, 'Provider rate limit was reached.');
        }

        if ($response->serverError()) {
            throw new RetryableSentimentAnalysisException(AnalysisErrorCategory::ProviderUnavailable, 'Provider service is temporarily unavailable.');
        }

        if ($response->failed()) {
            throw new TerminalSentimentAnalysisException(AnalysisErrorCategory::RequestRejected, 'Provider request was rejected.');
        }

        try {
            $payload = $response->json();
        } catch (JsonException) {
            throw $this->invalidResponse();
        }

        if (! is_array($payload)) {
            throw $this->invalidResponse();
        }

        $content = $this->contentFromPayload($payload);
        try {
            $json = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw $this->invalidResponse();
        }
        $sentiment = $this->strictSentiment($json);
        $usage = is_array($payload['usage'] ?? null) ? $payload['usage'] : [];

        return new SentimentAnalysis(
            $sentiment['polarity'],
            $sentiment['score'],
            $sentiment['confidence'],
            $sentiment['reason'],
            $this->provider(),
            is_string($payload['model'] ?? null) ? $payload['model'] : $this->model,
            new TokenUsage(
                $this->tokenCount($usage['prompt_tokens'] ?? null),
                $this->tokenCount($usage['completion_tokens'] ?? null),
            ),
        );
    }

    public function provider(): string
    {
        return 'nvidia';
    }

    public function model(): string
    {
        return $this->model;
    }

    private function request(string $text): Response
    {
        return Http::withToken($this->apiKey)
            ->timeout(self::REQUEST_TIMEOUT_SECONDS)
            ->acceptJson()
            ->asJson()
            ->withOptions(['verify' => $this->verifySsl, 'allow_redirects' => false])
            ->post(rtrim($this->baseUrl, '/') . '/chat/completions', [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'Responde solo JSON estricto: {"polarity":"positive|neutral|negative","score":-1..1,"confidence":0..1,"reason":"..."}.'],
                    ['role' => 'user', 'content' => $text],
                ],
                'temperature' => 0,
                'max_tokens' => self::MAX_TOKENS,
                'stream' => false,
                'response_format' => ['type' => 'json_object'],
            ]);
    }

    /** @param array<mixed> $payload */
    private function contentFromPayload(array $payload): string
    {
        $choices = $payload['choices'] ?? null;
        $choice = is_array($choices) ? ($choices[0] ?? null) : null;
        $message = is_array($choice) ? ($choice['message'] ?? null) : null;
        $content = is_array($message) ? ($message['content'] ?? null) : null;
        if (! is_string($content) || trim($content) === '') {
            throw $this->invalidResponse();
        }

        return $content;
    }

    /** @return array{polarity: string, score: float, confidence: float, reason: string} */
    private function strictSentiment(mixed $json): array
    {
        if (! is_array($json)) {
            throw $this->invalidResponse();
        }

        $keys = array_keys($json);
        sort($keys);
        if ($keys !== ['confidence', 'polarity', 'reason', 'score']) {
            throw $this->invalidResponse();
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
            throw $this->invalidResponse();
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

    private function invalidResponse(): TerminalSentimentAnalysisException
    {
        return new TerminalSentimentAnalysisException(AnalysisErrorCategory::InvalidResponse, 'Provider response was invalid.');
    }
}
