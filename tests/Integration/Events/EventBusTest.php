<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Events;

use App\Events\ItHappened;
use App\Events\MyEventHandler;
use function Tempest\event;
use Tempest\Events\EventBus;
use Tempest\Events\EventBusConfig;
use Tempest\Testing\IntegrationTest;
use Tests\Tempest\Integration\Events\Fixtures\MyEventBusMiddleware;

class EventBusTest extends IntegrationTest
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
