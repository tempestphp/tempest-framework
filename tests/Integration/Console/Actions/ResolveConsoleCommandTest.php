<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Actions;

use Tempest\Console\Actions\ResolveConsoleCommand;
use Tempest\Console\ConsoleCommand;
use Tests\Tempest\Integration\Console\Fixtures\ArrayInputCommand;
use Tests\Tempest\Integration\Console\Fixtures\CommandWithMiddleware;
use Tests\Tempest\Integration\Console\Fixtures\CommandWithNonCommandMethods;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class ResolveConsoleCommandTest extends FrameworkIntegrationTestCase
{
    public function test_resolve_string_command(): void
    {
        $command = $this->container->invoke(ResolveConsoleCommand::class, command: 'array_input');

        $this->assertInstanceOf(ConsoleCommand::class, $command);
        $this->assertSame(ArrayInputCommand::class, $command->handler->getDeclaringClass()->getName());
        $this->assertSame('array_input', $command->getName());
    }

    public function test_resolve_string_with_colon_command(): void
    {
        $command = $this->container->invoke(ResolveConsoleCommand::class, command: 'with:middleware');

        $this->assertInstanceOf(ConsoleCommand::class, $command);
        $this->assertSame(CommandWithMiddleware::class, $command->handler->getDeclaringClass()->getName());
        $this->assertSame('with:middleware', $command->getName());
    }

    public function test_resolve_fqcn_command(): void
    {
        $command = $this->container->invoke(ResolveConsoleCommand::class, command: CommandWithMiddleware::class);

        $this->assertInstanceOf(ConsoleCommand::class, $command);
        $this->assertSame(CommandWithMiddleware::class, $command->handler->getDeclaringClass()->getName());
        $this->assertSame('with:middleware', $command->getName());
    }

    public function test_resolve_array_command(): void
    {
        $command = $this->container->invoke(ResolveConsoleCommand::class, command: [CommandWithMiddleware::class, '__invoke']);

        $this->assertInstanceOf(ConsoleCommand::class, $command);
        $this->assertSame(CommandWithMiddleware::class, $command->handler->getDeclaringClass()->getName());
        $this->assertSame('with:middleware', $command->getName());
    }

    public function test_resolve_implicit_string_command(): void
    {
        $command = $this->container->invoke(ResolveConsoleCommand::class, command: 'test:not-empty');

        $this->assertInstanceOf(ConsoleCommand::class, $command);
        $this->assertSame(CommandWithNonCommandMethods::class, $command->handler->getDeclaringClass()->getName());
        $this->assertSame('test:not-empty', $command->getName());
    }

    public function test_resolve_array_with_method_command(): void
    {
        $command = $this->container->invoke(ResolveConsoleCommand::class, command: [CommandWithNonCommandMethods::class, 'do']);

        $this->assertInstanceOf(ConsoleCommand::class, $command);
        $this->assertSame(CommandWithNonCommandMethods::class, $command->handler->getDeclaringClass()->getName());
        $this->assertSame('test:not-empty', $command->getName());
    }

    public function test_non_console_command_throw(): void
    {
        $this->expectExceptionMessage('Command not found.');

        $this->container->invoke(ResolveConsoleCommand::class, command: [CommandWithNonCommandMethods::class, 'empty']);
    }
}
