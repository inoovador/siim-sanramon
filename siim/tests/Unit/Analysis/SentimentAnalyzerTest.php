<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use SIIM\Application\Analysis\Exceptions\SentimentAnalysisException;
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

it('uses strict NVIDIA JSON with the configured secure request options', function (): void {
    Http::preventStrayRequests();
    Http::fake([
        'https://example.test/v1/chat/completions' => Http::response([
            'model' => 'sentiment-model',
            'choices' => [['message' => ['content' => '{"polarity":"positive","score":0.8,"confidence":0.9,"reason":"Opinión favorable"}']]],
            'usage' => ['prompt_tokens' => 11, 'completion_tokens' => 7],
        ]),
    ]);

    $result = (new NvidiaSentimentAnalyzer('test-key', 'https://example.test/v1', 'sentiment-model'))->analyze('Buen servicio');

    expect($result->polarity)->toBe('positive')->and($result->score)->toBe(0.8)->and($result->usage->inputTokens)->toBe(11);
    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://example.test/v1/chat/completions'
        && $request->hasHeader('Authorization', 'Bearer test-key')
        && $request['temperature'] === 0
        && $request['stream'] === false
        && $request['max_tokens'] === 180
        && $request['response_format'] === ['type' => 'json_object']);
});

it('retries retryable NVIDIA failures without sleeping in tests', function (): void {
    Http::preventStrayRequests();
    Http::fake([
        'https://retry.example.test/v1/chat/completions' => Http::sequence()
            ->push([], 503)
            ->push([
                'choices' => [['message' => ['content' => '{"polarity":"neutral","score":0,"confidence":0.5,"reason":"Sin tendencia"}']]],
                'usage' => [],
            ]),
    ]);
    $delays = [];

    $result = (new NvidiaSentimentAnalyzer('test-key', 'https://retry.example.test/v1', 'sentiment-model', sleeper: function (int $seconds) use (&$delays): void {
        $delays[] = $seconds;
    }))->analyze('Texto');

    expect($result->polarity)->toBe('neutral')->and($delays)->toBe([10]);
});

it('stops retrying before a sleep can exceed the analysis runtime budget', function (): void {
    Http::preventStrayRequests();
    Http::fake([
        'https://budget.example.test/v1/chat/completions' => Http::sequence()
            ->push([], 503)
            ->push([], 503),
    ]);
    $delays = [];
    $analyzer = new NvidiaSentimentAnalyzer(
        'test-key',
        'https://budget.example.test/v1',
        'sentiment-model',
        sleeper: function (int $seconds) use (&$delays): void {
            $delays[] = $seconds;
        },
        analysisTimeBudgetSeconds: 75,
    );

    expect(fn () => $analyzer->analyze('Texto'))->toThrow(SentimentAnalysisException::class, 'retry budget');
    expect($delays)->toBe([10]);
    Http::assertSentCount(2);
    expect(NvidiaSentimentAnalyzer::RETRY_DELAYS_SECONDS)->toBe([10, 60, 300]);
});

it('accepts reordered strict JSON keys but rejects numeric strings and malformed outer JSON', function (): void {
    Http::preventStrayRequests();
    Http::fake([
        'https://reordered.example.test/v1/chat/completions' => Http::response([
            'model' => 'returned-model',
            'choices' => [['message' => ['content' => '{"reason":"Orden válido","confidence":0.4,"score":0,"polarity":"neutral"}']]],
            'usage' => ['prompt_tokens' => 2, 'completion_tokens' => 3],
        ]),
        'https://numeric.example.test/v1/chat/completions' => Http::response([
            'choices' => [['message' => ['content' => '{"polarity":"positive","score":"0.5","confidence":0.8,"reason":"Inválido"}']]],
        ]),
        'https://outer.example.test/v1/chat/completions' => Http::response('{invalid', 200),
    ]);

    $valid = (new NvidiaSentimentAnalyzer('test-key', 'https://reordered.example.test/v1', 'sentiment-model'))->analyze('Texto');
    expect($valid->model)->toBe('returned-model')->and($valid->usage->inputTokens)->toBe(2)->and($valid->usage->outputTokens)->toBe(3);
    expect(fn () => (new NvidiaSentimentAnalyzer('test-key', 'https://numeric.example.test/v1', 'sentiment-model'))->analyze('Texto'))->toThrow(SentimentAnalysisException::class);
    expect(fn () => (new NvidiaSentimentAnalyzer('test-key', 'https://outer.example.test/v1', 'sentiment-model'))->analyze('Texto'))->toThrow(SentimentAnalysisException::class);
});

it('retries 429 and transport failures but never retries a non-retryable 4xx response', function (): void {
    Http::preventStrayRequests();
    $transportAttempts = 0;
    $rateDelays = [];
    $transportDelays = [];
    $rateAnalyzer = new NvidiaSentimentAnalyzer('test-key', 'https://rate.example.test/v1', 'sentiment-model', sleeper: function (int $seconds) use (&$rateDelays): void {
        $rateDelays[] = $seconds;
    });
    $transportAnalyzer = new NvidiaSentimentAnalyzer('test-key', 'https://transport.example.test/v1', 'sentiment-model', sleeper: function (int $seconds) use (&$transportDelays): void {
        $transportDelays[] = $seconds;
    });

    $rateCalls = 0;
    Http::fake(function (Request $request) use (&$rateCalls, &$transportAttempts): mixed {
        return match ($request->url()) {
            'https://rate.example.test/v1/chat/completions' => ++$rateCalls === 1
                ? Http::response([], 429)
                : Http::response(['choices' => [['message' => ['content' => '{"polarity":"neutral","score":0,"confidence":0.5,"reason":"Correcto"}']]]]),
            'https://transport.example.test/v1/chat/completions' => ++$transportAttempts === 1
                ? Http::failedConnection('offline')
                : Http::response(['choices' => [['message' => ['content' => '{"polarity":"neutral","score":0,"confidence":0.5,"reason":"Correcto"}']]]]),
            'https://client.example.test/v1/chat/completions' => Http::response([], 400),
            default => throw new LogicException('Unexpected fake URL.'),
        };
    });

    expect($rateAnalyzer->analyze('Texto')->polarity)->toBe('neutral')->and($rateDelays)->toBe([10]);
    expect($transportAnalyzer->analyze('Texto')->polarity)->toBe('neutral')->and($transportDelays)->toBe([10]);
    expect(fn () => (new NvidiaSentimentAnalyzer('test-key', 'https://client.example.test/v1', 'sentiment-model', sleeper: static function (): void {}))->analyze('Texto'))->toThrow(SentimentAnalysisException::class);
});
