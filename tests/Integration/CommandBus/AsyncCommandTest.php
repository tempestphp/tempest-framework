<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\CommandBus;

use Symfony\Component\Process\Process;
use Tempest\CommandBus\AsyncCommandRepositories\MemoryRepository;
use Tempest\CommandBus\CommandRepository;
use Tempest\Highlight\Themes\TerminalStyle;
use Tests\Tempest\Fixtures\Handlers\MyAsyncCommandHandler;
use Tests\Tempest\Integration\CommandBus\Fixtures\MyAsyncCommand;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use function Tempest\command;
use function Tempest\Support\arr;

/**
 * @internal
 */
final class AsyncCommandTest extends FrameworkIntegrationTestCase
{
    public function test_async_commands_are_stored_and_handled_afterwards(): void
    {
        $repository = new MemoryRepository();

        $this->container->singleton(
            CommandRepository::class,
            fn () => $repository,
        );

        MyAsyncCommandHandler::$isHandled = false;

        command(new MyAsyncCommand('Brent'));

        $pendingCommands = arr($repository->getPendingCommands());

        $this->assertCount(1, $pendingCommands);
        $command = $pendingCommands->first();
        $this->assertSame('Brent', $command->name);
        $this->assertFalse(MyAsyncCommandHandler::$isHandled);

        $this->console
            ->call('command:handle ' . $pendingCommands->keys()->first())
            ->assertSee('Done');

        $this->assertTrue(MyAsyncCommandHandler::$isHandled);
    }

    public function test_async_command_monitor(): void
    {
        $process = new Process(['php', 'tempest', 'command:monitor']);
        $process->start();

        $this->console->call('command:dispatch 1');

        sleep(1);

        $output = $this->getOutput($process);
        $this->assertStringContainsString('Monitoring for new commands', $output);
        $this->assertStringContainsString('started at', $output);
        $this->assertStringContainsString('finished at', $output);
        $this->assertStringContainsString('Done', $output);
        $process->stop();
    }

    public function test_async_failed_command_monitor(): void
    {
        $process = new Process(['php', 'tempest', 'command:monitor']);
        $process->start();

        $this->console->call('command:dispatch 1 --fail');

        sleep(1);

        $output = $this->getOutput($process);
        $this->assertStringContainsString('Monitoring for new commands', $output);
        $this->assertStringContainsString('started at', $output);
        $this->assertStringContainsString('failed at', $output);
        $this->assertStringContainsString('Failed command', $output);
        $process->stop();

        arr(glob(__DIR__ . '/../../../src/Tempest/CommandBus/src/stored-commands/*.failed.txt'))
            ->each(function (string $filename): void {
                unlink($filename);
            });
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
