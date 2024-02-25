<?php

declare(strict_types=1);

namespace Tests\Tempest\Events;

use App\Events\ItHappened;
use App\Events\MyEventHandler;
use Tempest\Events\EventBus;
use Tempest\Events\EventBusConfig;
use Tests\Tempest\TestCase;
use function Tempest\event;

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

    /** @test */
    public function event_bus_with_middleware()
    {
        MyEventBusMiddleware::$hit = false;

        $config = $this->container->get(EventBusConfig::class);

        $config->addMiddleware(new MyEventBusMiddleware());

        event(new ItHappened());

        $this->assertTrue(MyEventBusMiddleware::$hit);
    }
}
