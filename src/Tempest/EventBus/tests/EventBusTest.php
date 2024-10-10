<?php

declare(strict_types=1);

namespace Tempest\EventBus\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Tempest\Container\GenericContainer;
use Tempest\EventBus\CallableEventHandler;
use Tempest\EventBus\EventBus;
use Tempest\EventBus\EventBusConfig;
use Tempest\EventBus\EventHandler;
use Tempest\EventBus\GenericEventBus;
use Tempest\EventBus\Tests\Fixtures\ItHappened;
use Tempest\EventBus\Tests\Fixtures\MyEventBusMiddleware;
use Tempest\EventBus\Tests\Fixtures\MyEventHandler;
use Tempest\EventBus\Tests\Fixtures\MyService;
use function Tempest\get;
use function Tempest\listen;
use Tempest\Reflection\MethodReflector;

/**
 * @internal
 */
final class EventBusTest extends TestCase
{
    public function test_class_based_handlers(): void
    {
        $container = new GenericContainer();

        $handler = new EventHandler();
        $handler->setHandler(new MethodReflector(new ReflectionMethod(MyEventHandler::class, 'handleItHappened')));

        $config = new EventBusConfig(
            handlers: [
                ItHappened::class => [
                    new CallableEventHandler(ItHappened::class, $handler),
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

    public function test_closure_based_handlers(): void
    {
        $container = new GenericContainer();
        $container->singleton(MyService::class, fn () => new MyService('bar'));

        $called = null;

        $config = new EventBusConfig(
            handlers: [
                ItHappened::class => [
                    new CallableEventHandler(ItHappened::class, function (ItHappened $event, MyService $service) use (&$called): void {
                        $called = $service->value;
                    }),
                ],
            ],
            middleware: [
                MyEventBusMiddleware::class,
            ]
        );

        $eventBus = new GenericEventBus($container, $config);

        MyEventBusMiddleware::$hit = false;

        $eventBus->dispatch(new ItHappened());

        $this->assertSame('bar', $called);
        $this->assertTrue(MyEventBusMiddleware::$hit);
    }

    public function test_closure_based_handlers_using_listen_method(): void
    {
        $container = new GenericContainer();
        $config = new EventBusConfig();
        $eventBus = new GenericEventBus($container, $config);
        $hasHappened = false;

        $eventBus->listen('my-event', function (string $event) use (&$hasHappened): void {
            $hasHappened = true;
        });

        $eventBus->dispatch('my-event');

        $this->assertTrue($hasHappened);
    }

    public function test_closure_based_handlers_using_function(): void
    {
        GenericContainer::setInstance($container = new GenericContainer());

        $container->config($config = new EventBusConfig());
        $container->singleton(EventBus::class, new GenericEventBus($container, $config));

        $hasHappened = false;

        listen('my-event', function (string $event) use (&$hasHappened): void {
            $hasHappened = true;
        });

        get(EventBus::class)->dispatch('my-event');

        $this->assertTrue($hasHappened);
    }
}
