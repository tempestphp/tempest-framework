---
title: Command bus
keywords: "Experimental"
---

Tempest comes with a built-in command bus, which can be used to dispatch a command to its handler (synchronous or asynchronous). A command bus offers multiple advantages over a more direct approach to modelling processes: commands and their handlers can easily be tested in isolation, they are simple to serialize, and similar to the eventbus, the command bus also supports middleware.

## Commands and handlers

Commands themselves are simple data classes. They don't have to implement anything:

```php
// app/CreateUser.php

final readonly class CreateUser
{
    public function __construct(
        public string $name,
        public string $email,
        public string $passwordHash,
    ) {}
}
```

Just like controller actions and console commands, command handlers are discovered automatically: every method tagged with `#[CommandHandler]` will be registered as one. Tempest knows which command a method handles by looking at the type of the first parameter:

```php
// app/UserHandlers.php

use Tempest\CommandBus\CommandHandler;

final class UserHandlers
{
    #[CommandHandler]
    public function handleCreateUser(CreateUser $createUser): void
    {
        User::create(
            name: $createUser->name,
            email: $createUser->email,
            password: $createUser->passwordHash,
        );

        // Send mail…
    }
}
```

Note that handler method names can be anything: invokable methods, `handleCreateUser()`, `handle()`, `whateverYouWant()`, …

Dispatching a command can be done with the `command()` function:

```php
use function Tempest\command;

command(new CreateUser($name));
```

Alternatively to using the `command()` function, you can inject the `CommandBus`, and dispatch commands like so:

```php
// app/UserController.php

use Tempest\CommandBus\CommandBus;

final readonly class UserController
{
    public function __construct(
        private CommandBus $commandBus,
    ) {}

    public function create(): Response
    {
        // …

        $this->commandBus->dispatch(new CreateUser($name));
    }
}
```

## Async commands

:::warning
The asynchronous commands implementation of Tempest is currently experimental. Although you can use it, please note that it is not covered by our backwards compatibility promise.
:::

A common use case for Tempest's command bus is to dispatch asynchronous commands: commands that are executed by their handler in the background, outside the main PHP process. Making a command asynchronous is done by adding the `#[Async]` to your command object:

```php
// app/SendMail.php

use Tempest\CommandBus\Async;

#[Async]
final readonly class SendMail
{
    public function __construct(
        public string $to,
        public string $body,
    ) {}
}
```

Besides adding the `#[Async]` attribute, the flow remains exactly the same as if you were dispatching synchronous commands:

```php
use function Tempest\command;

command(new SendMail(
    to: 'brendt@stitcher.io',
    body: 'Hello!'
));
```

In order to _run_ an asynchronous command, you'll have to run the `tempest command:monitor` console command. This is a long-running process, and you will need to set it up as a daemon on your production server. As long as `command:monitor` is running, async commands will be handled in the background.

Note that async command handling is still an early feature, and will receive many improvements over time.

## Command bus middleware

Whenever commands are dispatched, they are passed to the command bus, which will pass the command along to each of its handlers. Similar to web requests and console commands, this command bus supports middleware. Command bus middleware can be used to, for example, do logging for specific commands, add metadata to commands, or anything else. Command bus middleware are classes that implement the `CommandBusMiddleware` interface, and look like this:

```php
// app/MyCommandBusMiddleware.php

use Tempest\CommandBus\CommandBusMiddleware;
use Tempest\CommandBus\CommandBusMiddlewareCallable;

class MyCommandBusMiddleware implements CommandBusMiddleware
{
    public function __construct(
        private Logger $logger,
    ) {}

    public function __invoke(object $command, CommandBusMiddlewareCallable $next): void
    {
        $next($command);

        if ($command instanceof ShouldBeLogged) {
            $this->logger->info($command->getLogMessage());
        }
    }
}
```

### Middleware priority

All command bus middleware classes get sorted based on their priority. By default, each middleware gets the "normal" priority, but you can override it using the `#[Priority]` attribute:

```php
use Tempest\Core\Priority;

#[Priority(Priority::HIGH)]
final readonly class MyCommandBusMiddleware implements CommandBusMiddleware
{ /* … */ }
```

Note that priority is defined using an integer. You can however use one of the built-in `Priority` constants: `Priority::FRAMEWORK`, `Priority::HIGHEST`, `Priority::HIGH`, `Priority::NORMAL`, `Priority::LOW`, `Priority::LOWEST`.

### Middleware discovery

Global command bus middleware classes are discovered and sorted based on their priority. You can make a middleware class non-global by adding the `#[SkipDiscovery]` attribute:

```php
use Tempest\Discovery\SkipDiscovery;

#[SkipDiscovery]
final readonly class MyCommandBusMiddleware implements CommandBusMiddleware
{ /* … */ }
```
