<?php

declare(strict_types=1);

namespace SIIM\Application\Conversation;

use SIIM\Application\Shared\Contracts\AssistantProvider;
use SIIM\Application\Shared\Contracts\AssistantReply;

final class AssistantChatUseCase
{
    public function __construct(
        private readonly AssistantProvider $provider,
    ) {
    }

    /**
     * @param  list<array{role:string, content:string}>  $history
     */
    public function handle(string $systemPrompt, array $history, string $userMessage): AssistantReply
    {
        $messages = [['role' => 'system', 'content' => $systemPrompt]];

        foreach ($history as $msg) {
            if (in_array($msg['role'], ['user', 'assistant'], true) && $msg['content'] !== '') {
                $messages[] = ['role' => $msg['role'], 'content' => $msg['content']];
            }
        }

        $messages[] = ['role' => 'user', 'content' => $userMessage];

        return $this->provider->chat($messages);
    }
}
