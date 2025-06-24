<?php

declare(strict_types=1);

namespace Tempest\EventBus\Tests;

use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Tempest\Container\GenericContainer;
use Tempest\Core\Middleware;
use Tempest\EventBus\CallableEventHandler;
use Tempest\EventBus\EventBus;
use Tempest\EventBus\EventBusConfig;
use Tempest\EventBus\EventHandler;
use Tempest\EventBus\GenericEventBus;
use Tempest\EventBus\Tests\Fixtures\EventInterface;
use Tempest\EventBus\Tests\Fixtures\EventInterfaceHandler;
use Tempest\EventBus\Tests\Fixtures\EventInterfaceImplementation;
use Tempest\EventBus\Tests\Fixtures\ItHappened;
use Tempest\EventBus\Tests\Fixtures\MyEventBusMiddleware;
use Tempest\EventBus\Tests\Fixtures\MyEventHandler;
use Tempest\EventBus\Tests\Fixtures\MyService;
use Tempest\Reflection\MethodReflector;

use function Tempest\get;
use function Tempest\listen;

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
            middleware: new Middleware(
                MyEventBusMiddleware::class,
            ),
        );

        $eventBus = new GenericEventBus($container, $config);

        MyEventHandler::$itHappened = false;
        MyEventBusMiddleware::$hits = 0;

        $eventBus->dispatch(new ItHappened());

        $this->assertTrue(MyEventHandler::$itHappened);
        $this->assertSame(1, MyEventBusMiddleware::$hits);
    }

    public function test_middleware_is_only_triggered_once_per_event_dispatch(): void
    {
        $container = new GenericContainer();

        $handler = new EventHandler();
        $handler->setHandler(new MethodReflector(new ReflectionMethod(MyEventHandler::class, 'handleItHappened')));

        $config = new EventBusConfig(
            handlers: [
                ItHappened::class => [
                    new CallableEventHandler(ItHappened::class, $handler),
                    new CallableEventHandler(ItHappened::class, $handler),
                ],
            ],
            middleware: new Middleware(
                MyEventBusMiddleware::class,
            ),
        );

        $eventBus = new GenericEventBus($container, $config);

        MyEventBusMiddleware::$hits = 0;

        $eventBus->dispatch(new ItHappened());

        $this->assertSame(1, MyEventBusMiddleware::$hits);
    }

    public function test_closure_based_handlers(): void
    {
        $container = new GenericContainer();
        $container->singleton(MyService::class, fn () => new MyService('bar'));

        $called = null;

        $config = new EventBusConfig(
            handlers: [
                ItHappened::class => [
                    // @mago-expect best-practices/no-unused-parameter
                    new CallableEventHandler(ItHappened::class, function (ItHappened $event, MyService $service) use (&$called): void {
                        $called = $service->value;
                    }),
                ],
            ],
            middleware: new Middleware(
                MyEventBusMiddleware::class,
            ),
        );

        $eventBus = new GenericEventBus($container, $config);

        MyEventBusMiddleware::$hits = 0;

        $eventBus->dispatch(new ItHappened());

        $this->assertSame('bar', $called);
        $this->assertSame(1, MyEventBusMiddleware::$hits);
    }

    public function test_closure_based_handlers_using_listen_method(): void
    {
        $container = new GenericContainer();
        $config = new EventBusConfig();
        $eventBus = new GenericEventBus($container, $config);
        $hasHappened = false;

        // @mago-expect best-practices/no-unused-parameter
        $eventBus->listen(function (string $event) use (&$hasHappened): void {
            $hasHappened = true;
        }, event: 'my-event');

        $eventBus->dispatch('my-event');

        $this->assertTrue($hasHappened);
    }

    public function test_closure_based_handlers_using_listen_method_and_first_parameter(): void
    {
        $container = new GenericContainer();
        $config = new EventBusConfig();
        $eventBus = new GenericEventBus($container, $config);
        $hasHappened = false;

        // @mago-expect best-practices/no-unused-parameter
        $eventBus->listen(function (ItHappened $event) use (&$hasHappened): void {
            $hasHappened = true;
        });

        $eventBus->dispatch(new ItHappened());

        $this->assertTrue($hasHappened);
    }

    public function test_closure_based_handlers_using_function(): void
    {
        GenericContainer::setInstance($container = new GenericContainer());

        $container->config($config = new EventBusConfig());
        $container->singleton(EventBus::class, new GenericEventBus($container, $config));

        $hasHappened = false;

        // @mago-expect best-practices/no-unused-parameter
        listen(function (string $event) use (&$hasHappened): void {
            $hasHappened = true;
        }, event: 'my-event');

        get(EventBus::class)->dispatch('my-event');

        $this->assertTrue($hasHappened);
    }

    public function test_interface_handlers(): void
    {
        $container = new GenericContainer();

        $handler = new EventHandler();
        $handler->setHandler(new MethodReflector(new ReflectionMethod(EventInterfaceHandler::class, 'handleItHappened')));

        $config = new EventBusConfig(
            handlers: [
                EventInterface::class => [
                    new CallableEventHandler(EventInterface::class, $handler),
                ],
            ],
        );

        $eventBus = new GenericEventBus($container, $config);

        EventInterfaceHandler::$itHappened = false;

        $eventBus->dispatch(new EventInterfaceImplementation());

        $this->assertTrue(EventInterfaceHandler::$itHappened);
    }
}
