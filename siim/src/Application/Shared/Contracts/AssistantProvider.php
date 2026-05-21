<?php

declare(strict_types=1);

namespace SIIM\Application\Shared\Contracts;

interface AssistantProvider
{
    /**
     * @param  list<array{role: string, content: string}>  $messages
     */
    public function chat(array $messages): AssistantReply;

    public function name(): string;

    public function modelId(): string;
}
