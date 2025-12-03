<?php

namespace Tempest\Testing\Tests;

use Tempest\Testing\Test;
use Tempest\Testing\Testers\TestsEvents;
use Tempest\Testing\Tests\Fixtures\TestEvent;

use function Tempest\event;

final class TestsEventsTest
{
    use TestsEvents;

    #[Test]
    public function was_dispatched(): void
    {
        $this->events->preventPropagation();

        event(new TestEvent());

        $this->events->wasDispatched(TestEvent::class);
    }

    #[Test]
    public function was_not_dispatched(): void
    {
        $this->events->preventPropagation();

        $this->events->wasNotDispatched(TestEvent::class);
    }
}
