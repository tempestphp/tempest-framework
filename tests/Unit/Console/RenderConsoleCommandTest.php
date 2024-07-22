<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Console;

use ReflectionMethod;
use Tempest\Console\Actions\RenderConsoleCommand;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\GenericConsole;
use Tempest\Console\Highlight\TextTerminalTheme;
use Tempest\Console\Input\UnsupportedInputBuffer;
use Tempest\Console\Output\MemoryOutputBuffer;
use Tempest\Highlight\Highlighter;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Tests\Tempest\Unit\Console\Fixtures\MyConsole;

/**
 * @internal
 * @small
 */
class RenderConsoleCommandTest extends FrameworkIntegrationTestCase
{
    public function test_render()
    {
        $handler = new ReflectionMethod(new MyConsole(), 'handle');

        $consoleCommand = $handler->getAttributes(ConsoleCommand::class)[0]->newInstance();

        $consoleCommand->setHandler($handler);

        $output = new MemoryOutputBuffer();

        $highlighter = new Highlighter(new TextTerminalTheme());

        $console = new GenericConsole(
            output: $output,
            input: new UnsupportedInputBuffer(),
            highlighter: $highlighter,
        );

        (new RenderConsoleCommand($console))($consoleCommand);

        $this->assertSame(
            'test <path> [times=1] [--force=false] - description',
            trim($output->getBufferWithoutFormatting()[0]),
        );
    }
}
