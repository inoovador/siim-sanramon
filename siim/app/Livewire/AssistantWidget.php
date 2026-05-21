<?php

declare(strict_types=1);

namespace App\Livewire;

use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Validate;
use Livewire\Component;
use SIIM\Application\Conversation\AssistantChatUseCase;
use Throwable;

final class AssistantWidget extends Component
{
    public bool $isOpen = false;

    #[Validate('required|string|min:1|max:1000')]
    public string $input = '';

    public bool $waiting = false;

    public ?string $errorMessage = null;

    public ?string $pendingMessage = null;

    /** @var list<array{role:string, content:string, at:string}> */
    public array $history = [];

    public function mount(): void
    {
        if (session()->has('siim_assistant_history')) {
            $this->history = session('siim_assistant_history');
        } else {
            $this->history = [[
                'role' => 'assistant',
                'content' => 'Buen día. Soy el Asistente SIIM. ¿En qué le puedo ayudar hoy?',
                'at' => now()->format('H:i'),
            ]];
            session(['siim_assistant_history' => $this->history]);
        }
    }

    public function toggle(): void
    {
        $this->isOpen = ! $this->isOpen;
    }

    /**
     * First roundtrip: append user message immediately + clear input.
     * Triggers JS event that fires fetchReply on second roundtrip.
     */
    public function send(): void
    {
        $this->errorMessage = null;
        $this->validate();

        $userText = trim($this->input);
        if ($userText === '') {
            return;
        }

        $this->history[] = [
            'role' => 'user',
            'content' => $userText,
            'at' => now()->format('H:i'),
        ];
        $this->input = '';
        $this->waiting = true;
        $this->pendingMessage = $userText;
        $this->persistHistory();

        $this->dispatch('assistant:fetch-reply');
    }

    /**
     * Second roundtrip: do the slow LLM call and append assistant reply.
     */
    public function fetchReply(AssistantChatUseCase $useCase): void
    {
        if ($this->pendingMessage === null || $this->pendingMessage === '') {
            $this->waiting = false;

            return;
        }

        $userText = $this->pendingMessage;
        $this->pendingMessage = null;

        try {
            $maxHistory = (int) config('llm.assistant.max_history_messages', 20);
            $beforeUser = array_slice($this->history, 0, -1);
            $context = array_slice($beforeUser, -$maxHistory);

            $reply = $useCase->handle(
                systemPrompt: (string) config('llm.assistant.system_prompt'),
                history: array_map(
                    fn (array $m): array => ['role' => $m['role'], 'content' => $m['content']],
                    $context
                ),
                userMessage: $userText,
            );

            $this->history[] = [
                'role' => 'assistant',
                'content' => $reply->content,
                'at' => now()->format('H:i'),
            ];
        } catch (Throwable $e) {
            Log::error('Asistente SIIM falló', ['error' => $e->getMessage()]);
            $this->errorMessage = 'No pude responder en este momento. Intenta nuevamente.';
        } finally {
            $this->waiting = false;
            $this->persistHistory();
        }
    }

    public function reset_chat(): void
    {
        $this->history = [[
            'role' => 'assistant',
            'content' => 'Buen día. Soy el Asistente SIIM. ¿En qué le puedo ayudar hoy?',
            'at' => now()->format('H:i'),
        ]];
        $this->errorMessage = null;
        $this->pendingMessage = null;
        $this->waiting = false;
        $this->persistHistory();
    }

    private function persistHistory(): void
    {
        session(['siim_assistant_history' => $this->history]);
    }

    public function render()
    {
        return view('livewire.assistant-widget');
    }
}
