<?php

declare(strict_types=1);

namespace SIIM\Infrastructure\Events;

use Illuminate\Contracts\Events\Dispatcher;
use SIIM\Application\Citizen\Contracts\CitizenEventPublisher;
use SIIM\Domain\Citizen\Events\CommentIngested;

final readonly class LaravelCitizenEventPublisher implements CitizenEventPublisher
{
    public function __construct(private Dispatcher $events) {}

    public function publish(CommentIngested $event): void
    {
        $this->events->dispatch($event);
    }
}
