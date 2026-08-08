<?php

declare(strict_types=1);

use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Volt\Volt;
use SIIM\Application\Shared\Contracts\AssistantProvider;
use SIIM\Application\Shared\Contracts\AssistantReply;

beforeEach(function (): void {
    (new RolesAndPermissionsSeeder)->run();
});

function ragChatAnalyst(): User
{
    $user = User::factory()->create();
    $user->assignRole('analyst');
    $user->markEmailAsVerified();

    return $user;
}

function ragChatProvider(?string $content): AssistantProvider
{
    return new class($content) implements AssistantProvider
    {
        public function __construct(private readonly ?string $content) {}

        public function chat(array $messages): AssistantReply
        {
            if ($this->content === null) {
                throw new RuntimeException('secret-provider-body');
            }

            return new AssistantReply($this->content, 10, 20, 'test-model');
        }

        public function name(): string
        {
            return 'test-provider';
        }

        public function modelId(): string
        {
            return 'test-model';
        }
    };
}

it('does not default to a retired NVIDIA model', function (): void {
    expect(config('llm.providers.nvidia_glm.model'))->not->toBe('z-ai/glm-5.1');
});

it('teaches the assistant about the public survey and the profile route', function (): void {
    expect(config('llm.assistant.system_prompt'))
        ->toBeString()
        ->toContain('/encuesta')
        ->toContain('/profile')
        ->toContain('No inventes métricas');
});

it('answers the rag chat with the real assistant provider instead of canned text', function (): void {
    app()->instance(AssistantProvider::class, ragChatProvider('Respuesta del proveedor real.'));

    Volt::actingAs(ragChatAnalyst())
        ->test('panel.rag-chat.index')
        ->set('input', '¿Cuántos comentarios negativos hay?')
        ->call('send')
        ->call('fetchReply')
        ->assertSet('waiting', false)
        ->assertSee('Respuesta del proveedor real.');
});

it('surfaces a safe message when the provider fails', function (): void {
    app()->instance(AssistantProvider::class, ragChatProvider(null));

    Volt::actingAs(ragChatAnalyst())
        ->test('panel.rag-chat.index')
        ->set('input', 'Hola')
        ->call('send')
        ->call('fetchReply')
        ->assertSet('waiting', false)
        ->assertSet('error', 'No pude responder en este momento. Intente nuevamente.')
        ->assertDontSee('secret-provider-body');
});
