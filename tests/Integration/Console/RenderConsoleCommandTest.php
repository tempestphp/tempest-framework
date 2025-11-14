<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console;

use ReflectionMethod;
use Tempest\Console\Actions\ExecuteConsoleCommand;
use Tempest\Console\Actions\RenderConsoleCommand;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\GenericConsole;
use Tempest\Console\Highlight\TextTerminalTheme;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Console\Input\UnsupportedInputBuffer;
use Tempest\Console\Output\MemoryOutputBuffer;
use Tempest\Highlight\Highlighter;
use Tempest\Reflection\MethodReflector;
use Tests\Tempest\Integration\Console\Fixtures\MyConsole;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class RenderConsoleCommandTest extends FrameworkIntegrationTestCase
{
    private GenericConsole $testConsole;

    private MemoryOutputBuffer $consoleOutput;

    protected function setUp(): void
    {
        parent::setUp();

        $this->consoleOutput = new MemoryOutputBuffer();

        $highlighter = new Highlighter(new TextTerminalTheme());

        $this->testConsole = new GenericConsole(
            output: $this->consoleOutput,
            input: new UnsupportedInputBuffer(),
            highlighter: $highlighter,
            executeConsoleCommand: $this->container->get(ExecuteConsoleCommand::class),
            argumentBag: $this->container->get(ConsoleArgumentBag::class),
        );
    }

    public function test_render(): void
    {
        $handler = new MethodReflector(new ReflectionMethod(new MyConsole(), 'handle'));

        $consoleCommand = $handler->getAttributes(ConsoleCommand::class)[0];

        $consoleCommand->setHandler($handler);

        (new RenderConsoleCommand($this->testConsole))($consoleCommand);

        $this->assertSame(
            'test description',
            trim($this->consoleOutput->getBufferWithoutFormatting()[0]),
        );
    }

    public function test_render_arguments(): void
    {
        $handler = new MethodReflector(new ReflectionMethod(new MyConsole(), 'handle'));

        $consoleCommand = $handler->getAttributes(ConsoleCommand::class)[0];

        $consoleCommand->setHandler($handler);

        $renderConsoleCommand = new RenderConsoleCommand(
            console: $this->testConsole,
            renderArguments: true,
        );

        $renderConsoleCommand($consoleCommand);

        $this->assertSame(
            'test <path> <type {a|b|c}> [fallback=a {a|b|c}] [nullable-enum=null {a|b|c}] [times=1] [--force=false] [optional=null] description',
            trim($this->consoleOutput->getBufferWithoutFormatting()[0]),
        );
    }
}
