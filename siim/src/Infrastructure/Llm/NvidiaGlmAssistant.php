<?php

declare(strict_types=1);

namespace SIIM\Infrastructure\Llm;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use JsonException;
use RuntimeException;
use SIIM\Application\Shared\Contracts\AssistantProvider;
use SIIM\Application\Shared\Contracts\AssistantReply;

final class NvidiaGlmAssistant implements AssistantProvider
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $baseUrl,
        private readonly string $model,
        private readonly int $timeout = 30,
        private readonly int $maxTokens = 4096,
        private readonly bool $verifySsl = true,
        string $environment = 'production',
    ) {
        NvidiaTransportPolicy::assertSafe($baseUrl, $verifySsl, $environment);

        if ($apiKey === '') {
            throw new RuntimeException('NVIDIA provider is not configured.');
        }
    }

    public function chat(array $messages): AssistantReply
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->timeout($this->timeout)
                ->acceptJson()
                ->asJson()
                ->withOptions(['verify' => $this->verifySsl, 'allow_redirects' => false])
                ->post(rtrim($this->baseUrl, '/') . '/chat/completions', [
                    'model' => $this->model,
                    'messages' => $messages,
                    'temperature' => 0.7,
                    'top_p' => 1,
                    'max_tokens' => $this->maxTokens,
                    'stream' => false,
                ]);
        } catch (ConnectionException) {
            throw $this->safeException(0, 'connection_error');
        }

        if (! $response->successful()) {
            throw $this->safeException($response->status(), $this->errorCategory($response), $response);
        }

        try {
            $payload = $response->json();
        } catch (JsonException) {
            throw $this->safeException($response->status(), 'invalid_response', $response);
        }
        if (! is_array($payload)) {
            throw $this->safeException($response->status(), 'invalid_response', $response);
        }

        $content = $payload['choices'][0]['message']['content'] ?? '';
        $usage = is_array($payload['usage'] ?? null) ? $payload['usage'] : [];
        if (! is_string($content) || trim($content) === '') {
            throw $this->safeException($response->status(), 'invalid_response', $response);
        }

        return new AssistantReply(
            content: $content,
            tokensInput: (int) ($usage['prompt_tokens'] ?? 0),
            tokensOutput: (int) ($usage['completion_tokens'] ?? 0),
            model: is_string($payload['model'] ?? null) ? $payload['model'] : $this->model,
        );
    }

    public function name(): string
    {
        return 'nvidia_glm';
    }

    public function modelId(): string
    {
        return $this->model;
    }

    private function errorCategory(Response $response): string
    {
        if ($response->status() === 429) {
            return 'rate_limited';
        }

        return $response->serverError() ? 'upstream_error' : 'request_rejected';
    }

    private function safeException(int $status, string $category, ?Response $response = null): RuntimeException
    {
        $requestId = $response === null ? 'unavailable' : $this->safeRequestId($response->header('x-request-id'));

        return new RuntimeException("NVIDIA request failed (status={$status} category={$category} request_id={$requestId}).");
    }

    private function safeRequestId(?string $requestId): string
    {
        if ($requestId === null || preg_match('/\A[A-Za-z0-9][A-Za-z0-9._:-]{0,127}\z/D', $requestId) !== 1) {
            return 'unavailable';
        }

        return $requestId;
    }
}
