---
title: Event bus
description: "Learn how to use Tempest's built-in event bus to dispatch events and decouple different components in your application."
---

## Overview

An event bus is a synchronous communication system that allows different parts of an application to interact while being decoupled from each other.

In Tempest, events can be anything from a scalar value to a simple data class. An event handler can be a closure or a class method, the former needing manual registration and the latter being automatically discovered by the framework.

## Defining events

Most events are typically simple data classes that store information relevant to the event. As a best practice, they should not include any logic.

```php app/AircraftRegistered.php
final readonly class AircraftRegistered
{
    public function __construct(
        public string $registration,
    ) {}
}
```

When event classes are too much, you may also use scalar values—such as strings or enumerations—to define events. The latter is highly recommended for a better experience.

```php app/AircraftLifecycle.php
enum AircraftLifecycle
{
    case REGISTERED;
    case RETIRED;
}
```

## Dispatching events

The {`Tempest\EventBus\EventBus`} interface implements a `dispatch()` method, which you may use to dispatch any event. The event bus may be [injected as a dependency](../1-essentials/01-container) like any other service:

```php app/AircraftService.php
use Tempest\EventBus\EventBus;

final readonly class AircraftService
{
    public function __construct(
        public EventBus $eventBus,
    ) {}

    public function register(Aircraft $aircraft): void
    {
        // …

        $this->eventBus->dispatch(new AircraftRegistered(
            registration: $aircraft->icao_code,
        ));
    }
}
```

Alternatively, Tempest also provides the `\Tempest\event()` function. It accepts the same arguments as the {`Tempest\EventBus\EventBus`}'s `dispatch()` method, but uses [service location](../1-essentials/01-container#injected-properties) under the hood to access the event bus.

## Handling events

Events are only useful if they are listened for. In Tempest, this is done by calling the `listen()` method on the {b`Tempest\EventBus\EventBus`} instance, or by using the {b`#[Tempest\EventBus\EventHandler]`} attribute.

### Global handlers

Attribute-based event handling is most useful when events should be listened to application-wide. In other words, this is the option you should adopt when the associated event must be acted on every time it is dispatched.

```php app/AircraftObserver.php
final readonly class AircraftObserver
{
    #[EventHandler]
    public function onAircraftRegistered(AircraftRegistered $event): void
    {
        // …
    }
}
```

### Local handlers

When an event is only meant to be listened for in a specific situation, it is better to register it only when relevant. Such a situation could be, for instance, a [console command](../3-console/01-introduction) that needs logging when an event is dispatched.

```php app/SyncUsersCommand.php
final readonly class SyncUsersCommand
{
    public function __construct(
      private readonly Console $console,
      private readonly UserService $userService,
      private readonly EventBus $eventBus,
    ) {}

    #[ConsoleCommand('users:sync')]
    public function __invoke(AircraftRegistered $event): void
    {
        $this->console->header('Synchronizing users');

        // Listen for the UserSynced to write to the console when it happens
        $this->eventBus->listen(function (UserSynced $event) {
            $this->console->keyValue($event->fullName, 'SYNCED');
        });

        // Call external code that dispatches the UserSynced event
        $this->userService->synchronize();
    }
}
```

## Event middleware

When an event is dispatched, it is sent to the event bus, which then forwards it to all registered handlers. Similar to web requests and console commands, the event bus supports middleware.

Event bus middleware can be used for various purposes, such as logging specific events, adding metadata, or performing other pre—or post-processing tasks. These middleware are defined as classes that implement the {`Tempest\EventBus\EventBusMiddleware`} interface.

```php app/EventLoggerMiddleware.php
use Tempest\EventBus\EventBusMiddleware;
use Tempest\EventBus\EventBusMiddlewareCallable;

final readonly class EventLoggerMiddleware implements EventBusMiddleware
{
    public function __construct(
        private Logger $logger,
    ) {}

    public function __invoke(string|object $event, EventBusMiddlewareCallable $next): void
    {
        $next($event);

        if ($event instanceof ShouldBeLogged) {
            $this->logger->info($event->getLogMessage());
        }
    }
}
```

### Middleware priority

All event bus middleware classes get sorted based on their priority. By default, each middleware gets the "normal" priority, but you can override it using the `#[Priority]` attribute:

```php
use Tempest\Core\Priority;

#[Priority(Priority::HIGH)]
final readonly class EventLoggerMiddleware implements EventBusMiddleware
{ /* … */ }
```

Note that priority is defined using an integer. You can however use one of the built-in `Priority` constants: `Priority::FRAMEWORK`, `Priority::HIGHEST`, `Priority::HIGH`, `Priority::NORMAL`, `Priority::LOW`, `Priority::LOWEST`.

### Middleware discovery

Global event bus middleware classes are discovered and sorted based on their priority. You can make a middleware class non-global by adding the `#[SkipDiscovery]` attribute:

```php
use Tempest\Discovery\SkipDiscovery;

#[SkipDiscovery]
final readonly class EventLoggerMiddleware implements EventBusMiddleware
{ /* … */ }
```

## Stopping event propagation

In rare cases you might want an event only to be handled by a single handler. You can use the `b{Tempest\EventBus\StopsPropagation}` attribute on both events and event handlers to achieve this:

```php
use Tempest\EventBus\StopsPropagation;

#[StopsPropagation]
final class MyEvent {}
```

```php
use Tempest\EventBus\StopsPropagation;
use Tempest\EventBus\EventHandler;

final class MyHandler 
{   
    #[EventHandler]
    #[StopsPropagation]
    public function handle(OtherEvent $event): void
    {
        // …
    }
}
```

## Built-in framework events

Tempest includes a few built-in events that are primarily used internally. While most applications won’t need them, you are free to listen to them if desired.

Most notably, the {`\Tempest\Core\KernelEvent`} enumeration defines the `BOOTED` and `SHUTDOWN` events, which are dispatched when the framework has [finished bootstrapping](../4-internals/01-bootstrap) and right before the process is exited, respectively.

Other events include migration-related ones, such as {b`Tempest\Database\Migrations\MigrationMigrated`}, {b`Tempest\Database\Migrations\MigrationRolledBack`}, {b`Tempest\Database\Migrations\MigrationFailed`} and {b`Tempest\Database\Migrations\MigrationValidationFailed`}.

## Testing

By extending {b`Tempest\Framework\Testing\IntegrationTest`} from your test case, you gain access to the event bus testing utilities through the `eventBus` property.

These utilities include a way to replace the event bus with a testing implementation, as well as a few assertion methods to ensure that events have been dispatched or are being listened to.

```php
// Record dispatched events for assertion
$this->eventBus->recordEventDispatches();

// Prevents events from being handled
$this->eventBus->preventEventHandling();

// Assert that an event has been dispatched
$this->eventBus->assertDispatched(AircraftRegistered::class);

// Assert that an event has been dispatched multiple times
$this->eventBus->assertDispatched(AircraftRegistered::class, count: 2);

// Assert that an event has been dispatched,
// and make custom assertions on the event object
$this->eventBus->assertDispatched(
    event: AircraftRegistered::class,
    callback: fn (AircraftRegistered $event) => $event->registration === 'LX-JFA'
);

// Assert that an event has not been dispatched
$this->eventBus->assertNotDispatched(AircraftRegistered::class);

// Assert that an event has an attached handler
$this->eventBus->assertListeningTo(AircraftRegistered::class);
```

### Recording event dispatches

When testing code that dispatches events, you may want to prevent Tempest from handling them. This can be useful when the event’s handlers are tested separately, or when the side-effects of these handlers are not desired for this test case.

To disable event handling, the event bus instance must be replaced with a testing implementation in the container. This is achieved by calling the `preventEventHandling()` method on the `eventBus` property.

```php
$this->eventBus->preventEventHandling();
```

If you want to be able to make assertions while still allowing events to be dispatched, you may instead call the `recordEventDispatches()` method.

```php
$this->eventBus->recordEventDispatches();
```

### Testing a method-based handler

When handlers are registered as methods, instead of dispatching the corresponding event to test the handler logic, you may simply call the method to test it in isolation.

As an example, the following class contains an handler for the `AircraftRegistered` event:

```php app/AircraftObserver.php
final readonly class AircraftObserver
{
    #[EventHandler]
    public function onAircraftRegistered(AircraftRegistered $event): void
    {
        // …
    }
}
```

This handler may be tested by resolving the service class from the container, and calling the method with an instance of the event created for this purpose.

```php app/AircraftObserverTest.php
// Prevent events from being handled while allowing assertions
$this->eventBus->preventEventHandling();

// Resolve the service class
$observer = $this->container->get(AircraftObserver::class);

// Call the event handler
$observer->onAircraftRegistered(new AircraftRegistered(
    registration: 'LX-JFA',
));

// Assert that a mail has been sent, that the database contains something…
```
