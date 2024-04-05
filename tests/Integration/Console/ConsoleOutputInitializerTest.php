<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console;

use Tempest\Console\GenericConsoleOutput;
use Tempest\Console\Inititalizers\ConsoleOutputInitializer;
use Tempest\Console\NullConsoleOutput;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
class ConsoleOutputInitializerTest extends FrameworkIntegrationTestCase
{
    public function test_in_console_application()
    {
        $initializer = new ConsoleOutputInitializer();

        $this->actAsConsoleApplication();

        $consoleOutput = $initializer->initialize($this->container);

        $this->assertInstanceOf(GenericConsoleOutput::class, $consoleOutput);
    }

    public function test_in_http_application()
    {
        $initializer = new ConsoleOutputInitializer();

        $this->actAsHttpApplication();

        $consoleOutput = $initializer->initialize($this->container);

        $this->assertInstanceOf(NullConsoleOutput::class, $consoleOutput);
    }
}
