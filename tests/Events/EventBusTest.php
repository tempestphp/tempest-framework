<?php

namespace Tests\Tempest\Events;

use App\Events\ItHappened;
use App\Events\MyEventHandler;
use Tempest\Interface\EventBus;
use Tests\Tempest\TestCase;

class EventBusTest extends TestCase
{
    /** @test */
    public function it_works()
    {
        $eventBus = $this->container->get(EventBus::class);

        MyEventHandler::$itHappened = false;

        $eventBus->dispatch(new ItHappened());

        $this->assertTrue(MyEventHandler::$itHappened);
    }
}