<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\CommandBus;

use Symfony\Component\Process\Process;
use function Tempest\command;
use Tempest\CommandBus\AsyncCommandRepositories\MemoryRepository;
use Tempest\CommandBus\AsyncCommandRepository;
use Tempest\Highlight\Themes\TerminalStyle;
use Tests\Tempest\Fixtures\Handlers\MyAsyncCommandHandler;
use Tests\Tempest\Integration\CommandBus\Fixtures\MyAsyncCommand;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class AsyncCommandTest extends FrameworkIntegrationTestCase
{
    public function test_async_commands_are_stored_and_handled_afterwards(): void
    {
        $repository = new MemoryRepository();

        $this->container->singleton(
            AsyncCommandRepository::class,
            fn () => $repository
        );

        MyAsyncCommandHandler::$isHandled = false;

        command(new MyAsyncCommand('Brent'));

        $uuids = $repository->available();

        $this->assertCount(1, $uuids);
        $uuid = $uuids[0];
        $command = $repository->find($uuid);
        $this->assertSame('Brent', $command->name);
        $this->assertFalse(MyAsyncCommandHandler::$isHandled);

        $this->console
            ->call("command:handle {$uuid}")
            ->assertSee('Done');

        $this->assertEmpty($repository->available());
        $this->assertTrue(MyAsyncCommandHandler::$isHandled);
    }

    public function test_async_command_monitor(): void
    {
        $process = new Process(['php', 'tempest', 'command:monitor']);
        $process->start();

        $this->console->call("command:dispatch 1");

        sleep(1);

        $output = $this->getOutput($process);

        $this->assertStringContainsString('Monitoring for new commands', $output);
        $this->assertStringContainsString('started at', $output);
        $this->assertStringContainsString('finished at', $output);
    }

    private function getOutput(Process $process): string
    {
        $pattern = array_map(
            fn (TerminalStyle $consoleStyle) => TerminalStyle::ESC->value . $consoleStyle->value,
            TerminalStyle::cases(),
        );

        return str_replace($pattern, '', $process->getOutput());
    }
}
