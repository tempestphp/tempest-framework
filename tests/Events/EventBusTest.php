<?php

declare(strict_types=1);

namespace Tests\Tempest\Events;

use App\Events\ItHappened;
use App\Events\MyEventHandler;
use function Tempest\event;
use Tempest\Events\EventBusConfig;
use Tempest\Interface\EventBus;
use Tempest\Interface\EventBusMiddleware;
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


class MyEventBusMiddleware implements EventBusMiddleware
{
    public static bool $hit = false;

    public function __invoke(object $event, callable $next): void
    {
        self::$hit = true;

        $next($event);
    }
}
