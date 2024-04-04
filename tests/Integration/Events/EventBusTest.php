<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Events;

use App\Events\ItHappened;
use App\Events\MyEventHandler;
use function Tempest\event;
use Tempest\Events\EventBus;
use Tempest\Events\EventBusConfig;
use Tests\Tempest\Integration\Events\Fixtures\MyEventBusMiddleware;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
class EventBusTest extends FrameworkIntegrationTestCase
{
    public function test_it_works()
    {
        $eventBus = $this->container->get(EventBus::class);

        MyEventHandler::$itHappened = false;

        $eventBus->dispatch(new ItHappened());

        $this->assertTrue(MyEventHandler::$itHappened);
    }

    public function test_event_bus_with_middleware()
    {
        MyEventBusMiddleware::$hit = false;

        $config = $this->container->get(EventBusConfig::class);

        $config->addMiddleware(new MyEventBusMiddleware());

        event(new ItHappened());

        $this->assertTrue(MyEventBusMiddleware::$hit);
    }
}
