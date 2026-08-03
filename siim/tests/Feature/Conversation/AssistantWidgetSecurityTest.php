<?php

declare(strict_types=1);

use App\Livewire\AssistantWidget;
use Illuminate\Support\Facades\Log;
use Mockery\Expectation;
use Psr\Log\LoggerInterface;
use SIIM\Application\Conversation\AssistantChatUseCase;
use SIIM\Application\Shared\Contracts\AssistantProvider;
use SIIM\Application\Shared\Contracts\AssistantReply;

it('logs only bounded assistant failure metadata', function (): void {
    $logger = Mockery::mock(LoggerInterface::class);
    /** @var Expectation $logExpectation */
    $logExpectation = $logger->shouldReceive('error');
    $logExpectation->once()->withArgs(function (string $message, array $context): bool {
        $encoded = json_encode([$message, $context], JSON_THROW_ON_ERROR);

        return ! str_contains($encoded, 'provider-secret-body')
            && ! str_contains($encoded, 'api-key-secret')
            && $context === ['category' => 'assistant_provider_failure'];
    });
    Log::swap($logger);
    $provider = new class implements AssistantProvider
    {
        public function chat(array $messages): AssistantReply
        {
            throw new RuntimeException('provider-secret-body api-key-secret');
        }

        public function name(): string
        {
            return 'failing-test-provider';
        }

        public function modelId(): string
        {
            return 'test-model';
        }
    };
    $widget = new AssistantWidget;
    $widget->history = [['role' => 'user', 'content' => 'Hola', 'at' => '10:00']];
    $widget->pendingMessage = 'Hola';

    $widget->fetchReply(new AssistantChatUseCase($provider));

    expect($widget->errorMessage)->toBe('No pude responder en este momento. Intenta nuevamente.');
});
