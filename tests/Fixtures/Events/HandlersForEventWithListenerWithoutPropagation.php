<?php

namespace Tests\Tempest\Fixtures\Events;

use Tempest\Container\Singleton;
use Tempest\EventBus\EventHandler;
use Tempest\EventBus\WithoutPropagation;

#[Singleton]
final class HandlersForEventWithListenerWithoutPropagation
{
    public int $count = 0;

    #[EventHandler, WithoutPropagation]
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
