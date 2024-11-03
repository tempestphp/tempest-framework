<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Exceptions;

use Exception;
use Tempest\Console\Exceptions\ConsoleExceptionHandler;
use Tempest\Console\Output\MemoryOutputBuffer;
use Tempest\Console\OutputBuffer;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class ConsoleExceptionHandlerTest extends FrameworkIntegrationTestCase
{
    public function test_render_console_exception(): void
    {
        $output = new MemoryOutputBuffer();

        $this->container->singleton(OutputBuffer::class, $output);

        $handler = $this->container->get(ConsoleExceptionHandler::class);

        $handler->handleException(new Exception('test message'));

        $output = $output->asUnformattedString();

        $this->assertStringContainsString('Exception', $output);
        $this->assertStringContainsString('test message', $output);
        $this->assertStringContainsString(__FILE__, $output);
        $this->assertStringContainsString('$handler->handle(new Exception(\'test message\')); <', $output);
    }
}
