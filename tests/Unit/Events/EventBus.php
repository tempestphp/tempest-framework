<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Events;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Tempest\Container\GenericContainer;
use Tempest\EventBus\EventBusConfig;
use Tempest\EventBus\EventHandler;
use Tempest\EventBus\GenericEventBus;
use Tests\Tempest\Fixtures\Events\ItHappened;
use Tests\Tempest\Fixtures\Events\MyEventHandler;
use Tests\Tempest\Unit\Events\Fixtures\MyEventBusMiddleware;

/**
 * @internal
 * @small
 */
class EventBus extends TestCase
{
    public function test_it_works(): void
    {
        $container = new GenericContainer();

        $handler = new EventHandler();
        $handler->setHandler(new ReflectionMethod(MyEventHandler::class, 'handleItHappened'));

        $config = new EventBusConfig(
            handlers: [
                ItHappened::class => [
                    $handler,
                ],
            ],
            middleware: [
                new MyEventBusMiddleware(),
            ]
        );

        $eventBus = new GenericEventBus($container, $config);

        MyEventHandler::$itHappened = false;
        MyEventBusMiddleware::$hit = false;

        $eventBus->dispatch(new ItHappened());

        $this->assertTrue(MyEventHandler::$itHappened);
        $this->assertTrue(MyEventBusMiddleware::$hit);
    }
}
