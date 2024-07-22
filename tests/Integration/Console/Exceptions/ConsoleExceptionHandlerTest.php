<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Exceptions;

use Exception;
use Tempest\Console\Exceptions\ConsoleExceptionHandler;
use Tempest\Console\GenericConsole;
use Tempest\Console\Highlight\TextTerminalTheme;
use Tempest\Console\Input\ConsoleArgumentBag;
use Tempest\Console\Input\UnsupportedInputBuffer;
use Tempest\Console\Output\MemoryOutputBuffer;
use Tempest\Highlight\Highlighter;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
class ConsoleExceptionHandlerTest extends FrameworkIntegrationTestCase
{
    public function test_render_console_exception(): void
    {
        $output = new MemoryOutputBuffer();

        $highlighter = new Highlighter(new TextTerminalTheme());

        $handler = new ConsoleExceptionHandler(
            new GenericConsole(
                output: $output,
                input: new UnsupportedInputBuffer(),
                highlighter: $highlighter,
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
