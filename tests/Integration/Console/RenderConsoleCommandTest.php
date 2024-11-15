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
    public function test_render(): void
    {
        $handler = new MethodReflector(new ReflectionMethod(new MyConsole(), 'handle'));

        $consoleCommand = $handler->getAttributes(ConsoleCommand::class)[0];

        $consoleCommand->setHandler($handler);

        $output = new MemoryOutputBuffer();

        $highlighter = new Highlighter(new TextTerminalTheme());

        $console = new GenericConsole(
            output: $output,
            input: new UnsupportedInputBuffer(),
            highlighter: $highlighter,
            executeConsoleCommand: $this->container->get(ExecuteConsoleCommand::class),
            argumentBag: $this->container->get(ConsoleArgumentBag::class),
        );

        (new RenderConsoleCommand($console))($consoleCommand);

        $this->assertSame(
            'test <path> <type {a|b|c}> [fallback=a {a|b|c}] [times=1] [--force=false] - description',
            trim($output->getBufferWithoutFormatting()[0]),
        );
    }
}
