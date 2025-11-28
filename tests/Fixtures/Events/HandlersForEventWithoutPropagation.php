<?php

namespace Tests\Tempest\Fixtures\Events;

use Tempest\Container\Singleton;
use Tempest\EventBus\EventHandler;

#[Singleton]
final class HandlersForEventWithoutPropagation
{
    public int $count = 0;

    #[EventHandler]
    public function a(EventWithoutPropagation $event): void
    {
        $this->count++;
    }

    #[EventHandler]
    public function b(EventWithoutPropagation $event): void
    {
        $this->count++;
    }
}
