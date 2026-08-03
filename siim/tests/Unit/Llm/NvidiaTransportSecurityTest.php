<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use SIIM\Infrastructure\Llm\NvidiaGlmAssistant;
use SIIM\Infrastructure\Llm\NvidiaSentimentAnalyzer;
use Tests\TestCase;

uses(TestCase::class);

it('rejects unsafe NVIDIA endpoints before either client can send a bearer token', function (string $endpoint): void {
    Http::preventStrayRequests();

    expect(fn () => new NvidiaSentimentAnalyzer('secret-key', $endpoint, 'model'))
        ->toThrow(InvalidArgumentException::class)
        ->and(fn () => new NvidiaGlmAssistant('secret-key', $endpoint, 'model'))
        ->toThrow(InvalidArgumentException::class);

    Http::assertNothingSent();
})->with([
    'plain HTTP' => 'http://api.example.test/v1',
    'URL credentials' => 'https://user:password@example.test/v1',
    'localhost' => 'https://localhost/v1',
    'localhost subdomain' => 'https://api.localhost/v1',
    'IPv4 loopback' => 'https://127.0.0.1/v1',
    'IPv4 private' => 'https://10.1.2.3/v1',
    'IPv4 link local' => 'https://169.254.1.2/v1',
    'IPv4 reserved' => 'https://192.0.2.10/v1',
    'IPv4 deprecated relay' => 'https://192.88.99.1/v1',
    'IPv4 decimal alternative' => 'https://2130706433/v1',
    'IPv4 hexadecimal alternative' => 'https://0x7f000001/v1',
    'IPv4 shorthand alternative' => 'https://127.1/v1',
    'IPv6 loopback' => 'https://[::1]/v1',
    'IPv6 private' => 'https://[fd00::1]/v1',
    'IPv6 link local' => 'https://[fe80::1]/v1',
    'IPv6 protocol assignments' => 'https://[2001:2::1]/v1',
    'IPv6 ORCHID' => 'https://[2001:10::1]/v1',
    'IPv6 6to4' => 'https://[2002::1]/v1',
]);

it('allows disabled TLS verification only in local and testing environments', function (): void {
    expect(fn () => new NvidiaSentimentAnalyzer('key', 'https://api.example.test/v1', 'model', false, 'production'))
        ->toThrow(InvalidArgumentException::class)
        ->and(fn () => new NvidiaGlmAssistant('key', 'https://api.example.test/v1', 'model', verifySsl: false, environment: 'staging'))
        ->toThrow(InvalidArgumentException::class);

    expect(new NvidiaSentimentAnalyzer('key', 'https://api.example.test/v1', 'model', false, 'testing'))
        ->toBeInstanceOf(NvidiaSentimentAnalyzer::class)
        ->and(new NvidiaGlmAssistant('key', 'https://api.example.test/v1', 'model', verifySsl: false, environment: 'local'))
        ->toBeInstanceOf(NvidiaGlmAssistant::class);
});

it('sanitizes assistant failures and disables redirects', function (): void {
    Http::preventStrayRequests();
    $options = [];
    Http::fake([
        'https://assistant.example.test/v1/chat/completions' => function (Request $request, array $requestOptions) use (&$options): mixed {
            $options = $requestOptions;

            return Http::response(['secret' => 'provider-secret-body'], 503, ['x-request-id' => 'req-safe_123']);
        },
    ]);
    $assistant = new NvidiaGlmAssistant('api-key-secret', 'https://assistant.example.test/v1', 'model');

    $failure = null;
    try {
        $assistant->chat([['role' => 'user', 'content' => 'Hola']]);
    } catch (RuntimeException $exception) {
        $failure = $exception;
    }
    expect($failure)->toBeInstanceOf(RuntimeException::class);
    $message = $failure?->getMessage();
    assert(is_string($message));
    expect($message)->toContain('status=503')
        ->toContain('category=upstream_error')
        ->toContain('request_id=req-safe_123');
    expect(str_contains($message, 'provider-secret-body'))->toBeFalse()
        ->and(str_contains($message, 'api-key-secret'))->toBeFalse();

    expect($options['allow_redirects'] ?? null)->toBeFalse();
});

it('never exposes a response body when assistant content is missing', function (): void {
    Http::preventStrayRequests();
    Http::fake([
        'https://assistant.example.test/v1/chat/completions' => Http::response(['private' => 'provider-secret-body'], 200, ['x-request-id' => '../unsafe']),
    ]);
    $assistant = new NvidiaGlmAssistant('api-key-secret', 'https://assistant.example.test/v1', 'model');

    $failure = null;
    try {
        $assistant->chat([['role' => 'user', 'content' => 'Hola']]);
    } catch (RuntimeException $exception) {
        $failure = $exception;
    }
    expect($failure)->toBeInstanceOf(RuntimeException::class);
    $message = $failure?->getMessage();
    assert(is_string($message));
    expect($message)->toContain('status=200')
        ->toContain('category=invalid_response')
        ->toContain('request_id=unavailable');
    expect(str_contains($message, 'provider-secret-body'))->toBeFalse()
        ->and(str_contains($message, 'api-key-secret'))->toBeFalse();
});
