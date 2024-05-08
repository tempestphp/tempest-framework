<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Exceptions;

use Exception;
use Tempest\Console\Components\UnsupportedComponentRenderer;
use Tempest\Console\ConsoleArgumentBag;
use Tempest\Console\Exceptions\ConsoleExceptionHandler;
use Tempest\Console\GenericConsole;
use Tempest\Console\Highlight\TextTerminalTheme;
use Tempest\Console\Input\UnsupportedInputBuffer;
use Tempest\Console\Output\MemoryOutputBuffer;
use Tempest\Highlight\Highlighter;
use Tests\Tempest\Console\TestCase;

/**
 * @internal
 * @small
 */
class ConsoleExceptionHandlerTest extends TestCase
{
    public function test_render_console_exception(): void
    {
        $output = new MemoryOutputBuffer();

        $highlighter = new Highlighter(new TextTerminalTheme());

        $handler = new ConsoleExceptionHandler(
            new GenericConsole(
                output: $output,
                input: new UnsupportedInputBuffer(),
                componentRenderer: new UnsupportedComponentRenderer(),
                highlighter: $highlighter
            ),
            highlighter: $highlighter,
            argumentBag: $this->container->get(ConsoleArgumentBag::class),
        );

        $handler->handle(new Exception('test message'));

        $output = $output->asUnformattedString();

        $this->assertStringContainsString('Exception', $output);
        $this->assertStringContainsString('test message', $output);
        $this->assertStringContainsString(__FILE__, $output);
        $this->assertStringContainsString('$handler->handle(new Exception(\'test message\')); <', $output);
    }
}
