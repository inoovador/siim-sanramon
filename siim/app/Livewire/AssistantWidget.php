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

    public function send(AssistantChatUseCase $useCase): void
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
        $this->persistHistory();

        try {
            $maxHistory = (int) config('llm.assistant.max_history_messages', 20);
            $recent = array_slice($this->history, -$maxHistory);

            $reply = $useCase->handle(
                systemPrompt: (string) config('llm.assistant.system_prompt'),
                history: array_map(
                    fn (array $m): array => ['role' => $m['role'], 'content' => $m['content']],
                    array_slice($recent, 0, -1)
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
