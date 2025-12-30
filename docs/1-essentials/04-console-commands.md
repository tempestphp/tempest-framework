---
title: Console commands
description: "Learn how to write console commands with a modern, minimal syntax. In Tempest, this is done using attributes, which are automatically discovered by the framework."
---

## Overview

Tempest leverages [discovery](./05-discovery.md) to find class methods tagged with the {b`#[Tempest\Console\ConsoleCommand]`} attribute. Such methods will automatically be available as console commands through the `./tempest` executable.

Additionally, Tempest supports [console middleware](#middleware), which makes it easier to build some console features.

## Creating console commands

A console command is defined by adding the {b`#[Tempest\Console\ConsoleCommand]`} attribute to any class method. Usually, this is done in a dedicated command class, but it can be any method in any class.

```php
final readonly class TrackOperatingAircraft
{
    #[ConsoleCommand(name: 'aircraft:track')]
    public function __invoke(): void
    {
        // …
    }
}
```

The command will be named after the class name and the method name. If you prefer, you may add a `name` argument to the {b`#[Tempest\Console\ConsoleCommand]`} attribute to give a dedicated name to the command.

You may learn more about [configuring commands](#configuring-commands) in the dedicated section.

### Writing to the output

You may use the {`Tempest\Console\Console`} interface to write to the output. You can do this by injecting it into your command class, or by using the {`Tempest\Console\HasConsole`} trait, which provides a `$console` property.

The console methods are documented, but you might use the following ones most often:

```php
// Writes a line to the output.
$this->console->writeln('Hello from Tempest!');

 // Writes an informational, error, or warning message.
$this->console->info('This is an informational message.');
$this->console->error('This is an error message.');
$this->console->warning('This is a warning.');

// Prompts for user input. Supports validation and multiple choices.
$this->console->ask('What should be the email?', validation: [new Email()]);

// Executes and reports the progress of a closure.
$this->console->task('Syncing...', $this->synchronize(...));
```

### Specifying an exit code

Optionally, console may return an exit code. By default, Tempest will infer the correct exit code, depending on whether the command was successful or not.

If you want more control over which exit code is returned, you may return an integer between 0 and 255. For convenience, Tempest comes with an {`Tempest\Console\ExitCode`} enumeration that has a handful of predefined exit codes, which are generally accepted to be standard.

```php
use Tempest\Console\ExitCode;

public function __invoke(): ExitCode
{
    if (! $this->hasBeenSetup()) {
        return ExitCode::ERROR;
    }

    // …

    return ExitCode::SUCCESS;
}
```

## Command arguments

The command definition is inferred by the method's parameters. This way, there is no need to remember a framework-specific syntax—this is simple, modern PHP.

```php
final readonly class TrackOperatingAircraft
{
    #[ConsoleCommand('aircraft:track')]
    public function __invoke(AircraftType $type, ?int $radius = null): void
    {
        // …
    }
}
```

All built-in types are supported, including enums. When a parameter is nullable, it is also optional when invoking the console command.

### Negating boolean arguments

You may negate boolean flags by prefixing them with `--no`.

For instance, if the command has a `$validate` parameter with a default value of `true`, using the `--no-validate` flag would set the value of `$validate` to `false`.

### Adding a description or an alias

You may provide the {b`#[Tempest\Console\ConsoleArgument]`} to any argument of the method definition. This may be used to describe the argument, change its name or specify an alias.

```php
final readonly class TrackOperatingAircraft
{
    #[ConsoleCommand(
        name: 'aircraft:track',
        description: 'Updates operating aircraft in the database'
    )]
    public function __invoke(
        #[ConsoleArgument(description: 'Specifies the type of aircraft to track')]
        AircraftType $type,
        #[ConsoleArgument(
            description: 'Specifies the maximum radius around HQ to track aircraft in',
            aliases: ['r']
        )]
        ?int $radius = null
    ): void
    {
        // …
    }
}
```

Argument description are visible when using the `--help` flag during command invokation.

```console
<dim>./</dim>tempest aircraft:track --help

<h1>// AIRCRAFT:TRACK</h1>
Updates operating aircraft in the database

<h1>// USAGE</h1>
aircraft:track <type {<em>pc12</em>|<em>pc24</em>}> [<em>radius</em>=null]

<u>type</u>
Specifies the type of aircraft to track

<u>radius (r)</u>
Specifies the maximum radius around HQ to track aircraft in
```

## Configuring commands

The {b`#[Tempest\Console\ConsoleCommand]`} attribute accepts a few arguments that may provide more context to the user or affect its functionality.

For instance, the `middleware` argument accepts a list of [middleware classes](#middleware) for this command.

### Adding a description

You may use the `description` argument on the {b`#[Tempest\Console\ConsoleCommand]`} attribute to provide context to users regarding the functionality of the command.

This description is shown when listing console commands or when calling it with the `--help` argument.

```php
final readonly class TrackOperatingAircraft
{
    #[ConsoleCommand(description: 'Updates operating aircraft in the database')]
    public function __invoke(): void
    {
        // …
    }
}
```

### Hiding the command

A command may be completely hidden from the command list by setting the `hidden` argument to `true`. The command will remain invokable, but will not be visible to the user when listing commands.

```php
final readonly class TrackOperatingAircraft
{
    #[ConsoleCommand(hidden: true)]
    public function __invoke(): void
    {
        // …
    }
}
```

### Specifying a name

The `name` argument of the {b`#[Tempest\Console\ConsoleCommand]`} attribute allows for configuring the command name. This is the name used for the command invokation, and the name that is displayed when listing all commands.

```php
final readonly class TrackOperatingAircraft
{
    #[ConsoleCommand('aircraft:track')]
    public function __invoke(): void
    {
        // …
    }
}
```

### Specifying aliases

When a command is used a lot, you may add aliases instead of shortening its name. To do this, use the `aliases` argument of the {b`#[Tempest\Console\ConsoleCommand]`} attribute.

```php
final readonly class TrackOperatingAircraft
{
    #[ConsoleCommand('aircraft:track', aliases: ['track'])]
    public function __invoke(AircraftType $type): void
    {
        // …
    }
}
```

You may then call the command by using this alias.

### Preventing usage in production

Some commands are dangerous to use in a non-local environment. You may add the {b`Tempest\Console\Middleware\CautionMiddleware`} to a command to prevent it from being invoked in production. When this happens, the user will be alerted and provided with the choice to continue or abort the command execution.

```php
final readonly class SynchronizeAircraft
{
    #[ConsoleCommand('aircraft:sync', middleware: [CautionMiddleware::class])]
    public function __invoke(): void
    {
        // …
    }
}
```

## Interactive components

Tempest console comes with a range of interactive components that can be used to interact with the user while running a console command:

- `$console->ask()` will prompt the user for input and validate it.
- `$console->confirm()` will prompt the user for a yes/no answer.
- `$console->password()` will prompt the user for a password, the input will be masked.
- `$console->progressBar()` will render a progress bar.
- `$console->search()` will prompt the user with a search bar and update a result list in real-time.
- `$console->task()` will run a task and show a progress bar while it's running.

:::warning
Interactive components are only supported on Mac and Linux. On Windows, Tempest will fall back to non-interactive versions of these components.
:::

## Middleware

Console middleware can be applied globally or on a per-command basis. Global console middleware will be discovered and applied automatically, by priority order.

### Building your own middleware

You may implement the {`Tempest\Console\ConsoleMiddleware`} interface to build a console middleware.

```php app/InspireMiddleware.php
use Tempest\Console\ConsoleMiddleware;
use Tempest\Console\ConsoleMiddlewareCallable;

final readonly class InspireMiddleware implements ConsoleMiddleware
{
    public function __construct(
        private InspirationService $inspiration,
        private Console $console,
    ) {}

    public function __invoke(Invocation $invocation, ConsoleMiddlewareCallable $next): ExitCode|int
    {
        if ($invocation->argumentBag->get('inspire')) {
            $this->console->writeln($this->inspiration->random());
        }

        return $next($invocation);
    }
}
```

Middleware classes will be autowired by the container, so you can use the constructor to inject any dependency you'd like. The {b`Tempest\Console\Initializers\Invocation`} object contains everything you need about the context for the current console command invocation:

- `{php}$invocation->argumentBag` contains the argument bag with all the input provided by the user.
- `{php}$invocation->consoleCommand` an instance of the {b`#[Tempest\Console\ConsoleCommand]`} attribute for the matched console command. This property will be `null` if you're not using {b`Tempest\Console\Middleware\ResolveOrRescueMiddleware`} or if your middleware runs before it.

#### Middleware priority

All console middleware classes get sorted based on their priority. By default, each middleware gets the normal priority, but you can override it using the {b`#[Tempest\Core\Priority]`} attribute:

```php app/InspireMiddleware.php
use Tempest\Core\Priority;

#[Priority(Priority::HIGH)]
final readonly class InspireMiddleware implements ConsoleMiddleware
{ /* … */ }
```

Note that priority is defined using an integer. However, the {b`Tempest\Core\Priority`} class provides a few constant with predefined priorities: `Priority::FRAMEWORK`, `Priority::HIGHEST`, `Priority::HIGH`, `Priority::NORMAL`, `Priority::LOW`, `Priority::LOWEST`.

#### Middleware discovery

Global console middleware classes are discovered and sorted based on their priority. You can make a middleware class non-global by using the {b`#[Tempest\Discovery\SkipDiscovery]`} attribute:

```php
use Tempest\Discovery\SkipDiscovery;

#[SkipDiscovery]
final readonly class InspireMiddleware implements ConsoleMiddleware
{ /* … */ }
```

### Built-in middleware

Tempest provides a few built-in middleware that you may use on your console commands. Some of these middleware are used internally on some commands, and some of them are used on all commands.

- The {b`Tempest\Console\Middleware\ForceMiddleware`} adds the `--force` flag for skipping `{php}$console->confirm()` calls.
- The {b`Tempest\Console\Middleware\CautionMiddleware`} middleware [prevents usage of commands in production](#preventing-usage-in-production).
- The {b`Tempest\Console\Middleware\OverviewMiddleware`} is responsible from listing all commands when none is provided.
- The {b`Tempest\Console\Middleware\ResolveOrRescueMiddleware`} middleware provides a list of similar commands when an unknown command is invoked.
- The {b`Tempest\Console\Middleware\HelpMiddleware`} middleware provides help when the `--help` flag is used.
- The {b`Tempest\Console\Middleware\ConsoleExceptionMiddleware`} middleware catches and properly render console exceptions.

## Scheduling

Console commands—or any public class method—may be scheduled by using the {b`#[Tempest\Console\Schedule]`} attribute, which accepts an {b`Tempest\Console\Scheduler\Interval`} or {b`Tempest\Console\Scheduler\Every`} value. Methods with this attributes are automatically [discovered](./05-discovery.md), so there is nothing more to add.

You may read more on the [dedicated chapter](../2-features/11-scheduling.md).

## Testing

Tempest provides a console command testing utility accessible through the `console` property of the [`IntegrationTest`](https://github.com/tempestphp/tempest-framework/blob/main/src/Tempest/Framework/Testing/IntegrationTest.php) test case. You may learn more about testing in the [dedicated chapter](./07-testing.md).

```php tests/ExportUsersCommandTest.php
$this->console
    ->call(ExportUsersCommand::class)
    ->assertSuccess()
    ->assertSee('12 users exported');

$this->console
    ->call(WipeDatabaseCommand::class)
    ->assertSee('caution')
    ->submit()
    ->assertSuccess();
```
