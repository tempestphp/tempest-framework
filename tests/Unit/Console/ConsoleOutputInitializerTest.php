<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Console;

use Tempest\Console\ConsoleOutputInitializer;
use Tempest\Console\GenericConsoleOutput;
use Tempest\Console\NullConsoleOutput;
use Tests\Tempest\TestCase;

class ConsoleOutputInitializerTest extends TestCase
{
    /** @test */
    public function test_in_console_application()
    {
        $initializer = new ConsoleOutputInitializer();

        $this->actAsConsoleApplication();

        $consoleOutput = $initializer->initialize($this->container);

        $this->assertInstanceOf(GenericConsoleOutput::class, $consoleOutput);
    }

    /** @test */
    public function test_in_http_application()
    {
        $initializer = new ConsoleOutputInitializer();

        $this->actAsHttpApplication();

        $consoleOutput = $initializer->initialize($this->container);

        $this->assertInstanceOf(NullConsoleOutput::class, $consoleOutput);
    }
}
