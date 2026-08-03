<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use SIIM\Application\Analysis\Exceptions\RetryableSentimentAnalysisException;
use SIIM\Application\Analysis\Exceptions\TerminalSentimentAnalysisException;
use SIIM\Infrastructure\Llm\LexiconSentimentAnalyzer;
use SIIM\Infrastructure\Llm\NvidiaSentimentAnalyzer;
use Tests\TestCase;

uses(TestCase::class);

it('scores Spanish sentiment deterministically including negation', function (): void {
    $analyzer = new LexiconSentimentAnalyzer;

    expect($analyzer->analyze('Excelente servicio y buena atención')->polarity)->toBe('positive')
        ->and($analyzer->analyze('Pésimo servicio, nunca ayuda')->polarity)->toBe('negative')
        ->and($analyzer->analyze('No es bueno el servicio')->polarity)->toBe('negative')
        ->and($analyzer->analyze('Sin problemas, no malo')->polarity)->toBe('positive')
        ->and($analyzer->analyze('La oficina abre a las ocho')->polarity)->toBe('neutral');
});

it('uses strict NVIDIA JSON with the exact secure request options', function (): void {
    Http::preventStrayRequests();
    $capturedOptions = [];
    Http::fake([
        'https://example.test/v1/chat/completions' => function (Request $request, array $options) use (&$capturedOptions): mixed {
            $capturedOptions = $options;

            return Http::response([
                'model' => 'sentiment-model',
                'choices' => [['message' => ['content' => '{"polarity":"positive","score":0.8,"confidence":0.9,"reason":"Opinión favorable"}']]],
                'usage' => ['prompt_tokens' => 11, 'completion_tokens' => 7],
            ]);
        },
    ]);

    $result = (new NvidiaSentimentAnalyzer('test-key', 'https://example.test/v1', 'sentiment-model'))->analyze('Buen servicio');

    expect($result->polarity)->toBe('positive')
        ->and($result->score)->toBe(0.8)
        ->and($result->usage->inputTokens)->toBe(11)
        ->and($capturedOptions['timeout'] ?? null)->toBe(20)
        ->and($capturedOptions['verify'] ?? null)->toBeTrue()
        ->and($capturedOptions['allow_redirects'] ?? null)->toBeFalse();
    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://example.test/v1/chat/completions'
        && $request->hasHeader('Authorization', 'Bearer test-key')
        && $request['temperature'] === 0
        && $request['stream'] === false
        && $request['max_tokens'] === 180
        && $request['response_format'] === ['type' => 'json_object']
        && data_get($request->data(), 'messages.1') === ['role' => 'user', 'content' => 'Buen servicio']);
    Http::assertSentCount(1);
});

it('does not retry or sleep for retryable HTTP responses', function (int $status): void {
    Http::preventStrayRequests();
    Http::fake(['https://retryable.example.test/v1/chat/completions' => Http::response(['private' => 'provider-body'], $status)]);
    $analyzer = new NvidiaSentimentAnalyzer('test-key', 'https://retryable.example.test/v1', 'sentiment-model');

    expect(fn () => $analyzer->analyze('Texto'))->toThrow(RetryableSentimentAnalysisException::class);
    Http::assertSentCount(1);
})->with([[429], [500], [503]]);

it('propagates one connection failure to the database queue without an internal retry', function (): void {
    Http::preventStrayRequests();
    Http::fake(fn (): mixed => Http::failedConnection('offline'));
    $analyzer = new NvidiaSentimentAnalyzer('test-key', 'https://connection.example.test/v1', 'sentiment-model');

    expect(fn () => $analyzer->analyze('Texto'))->toThrow(RetryableSentimentAnalysisException::class);
    Http::assertSentCount(1);
});

it('does not retry or sleep for a terminal client response', function (): void {
    Http::preventStrayRequests();
    Http::fake(['https://client.example.test/v1/chat/completions' => Http::response(['private' => 'provider-body'], 400)]);
    $analyzer = new NvidiaSentimentAnalyzer('test-key', 'https://client.example.test/v1', 'sentiment-model');

    expect(fn () => $analyzer->analyze('Texto'))->toThrow(TerminalSentimentAnalysisException::class);
    Http::assertSentCount(1);
});

it('accepts reordered strict JSON keys and reports exact response metadata', function (): void {
    Http::preventStrayRequests();
    Http::fake([
        'https://reordered.example.test/v1/chat/completions' => Http::response([
            'model' => 'returned-model',
            'choices' => [['message' => ['content' => '{"reason":"Orden válido","confidence":0.4,"score":0,"polarity":"neutral"}']]],
            'usage' => ['prompt_tokens' => 2, 'completion_tokens' => 3],
        ]),
    ]);

    $valid = (new NvidiaSentimentAnalyzer('test-key', 'https://reordered.example.test/v1', 'sentiment-model'))->analyze('Texto');

    expect($valid->polarity)->toBe('neutral')
        ->and($valid->score)->toBe(0.0)
        ->and($valid->confidence)->toBe(0.4)
        ->and($valid->reason)->toBe('Orden válido')
        ->and($valid->provider)->toBe('nvidia')
        ->and($valid->model)->toBe('returned-model')
        ->and($valid->usage->inputTokens)->toBe(2)
        ->and($valid->usage->outputTokens)->toBe(3);
    Http::assertSentCount(1);
});

it('rejects malformed or non-strict sentiment content', function (string $content): void {
    Http::preventStrayRequests();
    Http::fake([
        'https://schema.example.test/v1/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => $content]]],
        ]),
    ]);

    expect(fn () => (new NvidiaSentimentAnalyzer('test-key', 'https://schema.example.test/v1', 'sentiment-model'))->analyze('Texto'))
        ->toThrow(TerminalSentimentAnalysisException::class);
    Http::assertSentCount(1);
})->with([
    'empty content' => '',
    'missing key' => '{"polarity":"neutral","score":0,"confidence":0.5}',
    'extra key' => '{"polarity":"neutral","score":0,"confidence":0.5,"reason":"Válido","extra":true}',
    'empty object' => '{}',
    'fenced JSON' => '```json {"polarity":"neutral","score":0,"confidence":0.5,"reason":"Válido"} ```',
    'prose-wrapped JSON' => 'Resultado: {"polarity":"neutral","score":0,"confidence":0.5,"reason":"Válido"}',
    'numeric string' => '{"polarity":"positive","score":"0.5","confidence":0.8,"reason":"Inválido"}',
    'non-finite score' => '{"polarity":"positive","score":1e400,"confidence":0.8,"reason":"Inválido"}',
    'out-of-range score' => '{"polarity":"positive","score":1.1,"confidence":0.8,"reason":"Inválido"}',
    'out-of-range confidence' => '{"polarity":"positive","score":0.5,"confidence":-0.1,"reason":"Inválido"}',
    'empty reason' => '{"polarity":"positive","score":0.5,"confidence":0.8,"reason":"   "}',
    'invalid reason type' => '{"polarity":"positive","score":0.5,"confidence":0.8,"reason":7}',
]);

it('rejects malformed outer NVIDIA JSON without a retry', function (): void {
    Http::preventStrayRequests();
    Http::fake(['https://outer.example.test/v1/chat/completions' => Http::response('{invalid', 200)]);

    expect(fn () => (new NvidiaSentimentAnalyzer('test-key', 'https://outer.example.test/v1', 'sentiment-model'))->analyze('Texto'))
        ->toThrow(TerminalSentimentAnalysisException::class);
    Http::assertSentCount(1);
});
