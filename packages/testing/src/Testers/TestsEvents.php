<?php

namespace Tempest\Testing\Testers;

use Tempest\Container\Container;
use Tempest\EventBus\EventBus;
use Tempest\Testing\After;
use Tempest\Testing\Before;

trait TestsEvents
{
    protected EventBusTester $events;

    protected EventBus $originalEventBus;

    #[Before]
    public function testsEventsBefore(Container $container): void
    {
        $this->originalEventBus = $container->get(EventBus::class);

        $this->events = new EventBusTester($this->originalEventBus);
        $container->singleton(EventBus::class, $this->events);
    }

    #[After]
    public function testsEventsAfter(Container $container): void
    {
        $container->singleton(EventBus::class, $this->originalEventBus);
    }
}
