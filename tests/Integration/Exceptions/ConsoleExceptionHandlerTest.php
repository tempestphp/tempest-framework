<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Exceptions;

use Tempest\Console\ConsoleOutput;
use Tempest\Console\Exceptions\ConsoleExceptionHandler;
use Tempest\Console\Testing\TestConsoleOutput;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
class ConsoleExceptionHandlerTest extends FrameworkIntegrationTestCase
{
    public function test_exception()
    {
        $this->appConfig->enableExceptionHandling = true;

        $output = new TestConsoleOutput();

        $this->container->singleton(ConsoleOutput::class, fn () => $output);

        $this->appConfig->exceptionHandlers = [
            $this->container->get(ConsoleExceptionHandler::class),
        ];

        $this->console->call('fail output');

        $lines = $output->getLinesWithoutFormatting();

        $this->assertSame('Exception', $lines[0]);
        $this->assertSame('A message from the exception output', $lines[1]);
        $this->assertStringContainsString('function failingFunction(string $string)', $lines[3]);
        $this->assertStringContainsString('/app/Console/FailCommand.php', $lines[5]);
    }
}
