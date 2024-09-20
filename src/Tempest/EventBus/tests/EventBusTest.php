<?php

declare(strict_types=1);

namespace Tempest\EventBus\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Tempest\Container\GenericContainer;
use Tempest\EventBus\EventBusConfig;
use Tempest\EventBus\EventHandler;
use Tempest\EventBus\GenericEventBus;
use Tempest\EventBus\Tests\Fixtures\ItHappened;
use Tempest\EventBus\Tests\Fixtures\MyEventBusMiddleware;
use Tempest\EventBus\Tests\Fixtures\MyEventHandler;
use Tempest\Reflection\MethodReflector;

/**
 * @internal
 */
final class EventBusTest extends TestCase
{
    public function test_it_works(): void
    {
        $container = new GenericContainer();

        $handler = new EventHandler();
        $handler->setHandler(new MethodReflector(new ReflectionMethod(MyEventHandler::class, 'handleItHappened')));

        $config = new EventBusConfig(
            handlers: [
                ItHappened::class => [
                    $handler,
                ],
            ],
            middleware: [
                MyEventBusMiddleware::class,
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
