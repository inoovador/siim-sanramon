<?php

declare(strict_types=1);

namespace SIIM\Infrastructure\Llm;

use Illuminate\Support\Facades\Http;
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
    ) {
        if ($apiKey === '') {
            throw new RuntimeException('NVIDIA_API_KEY no configurada en .env');
        }
    }

    public function chat(array $messages): AssistantReply
    {
        $response = Http::withToken($this->apiKey)
            ->timeout($this->timeout)
            ->acceptJson()
            ->asJson()
            ->withOptions(['verify' => $this->verifySsl])
            ->post($this->baseUrl . '/chat/completions', [
                'model' => $this->model,
                'messages' => $messages,
                'temperature' => 0.7,
                'top_p' => 1,
                'max_tokens' => $this->maxTokens,
                'stream' => false,
            ]);

        if ($response->failed()) {
            throw new RuntimeException(sprintf(
                'NVIDIA API falló [HTTP %d]: %s',
                $response->status(),
                $response->body(),
            ));
        }

        $payload = $response->json();
        $content = $payload['choices'][0]['message']['content'] ?? '';
        $usage = $payload['usage'] ?? [];

        if ($content === '') {
            throw new RuntimeException('NVIDIA API respondió sin contenido: ' . $response->body());
        }

        return new AssistantReply(
            content: $content,
            tokensInput: (int) ($usage['prompt_tokens'] ?? 0),
            tokensOutput: (int) ($usage['completion_tokens'] ?? 0),
            model: $payload['model'] ?? $this->model,
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
}
