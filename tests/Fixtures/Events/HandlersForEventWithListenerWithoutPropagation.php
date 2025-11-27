<?php

namespace Tests\Tempest\Fixtures\Events;

use Tempest\Container\Singleton;
use Tempest\EventBus\EventHandler;
use Tempest\EventBus\StopsPropagation;

#[Singleton]
final class HandlersForEventWithListenerWithoutPropagation
{
    public int $count = 0;

    #[EventHandler, StopsPropagation]
    public function a(EventForListenerWithoutPropagation $event): void
    {
        $this->count++;
    }

    #[EventHandler]
    public function b(EventForListenerWithoutPropagation $event): void
    {
        $this->count++;
    }
}
