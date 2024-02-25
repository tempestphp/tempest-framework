<?php

declare(strict_types=1);

namespace Tests\Tempest\Console;

use Tempest\AppConfig;
use Tempest\Application\Application;
use Tempest\Application\ConsoleApplication;
use Tempest\Application\HttpApplication;
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

        $this->container->singleton(Application::class, fn () => new ConsoleApplication(
            [],
            $this->container,
            $this->container->get(AppConfig::class),
        ));

        $consoleOutput = $initializer->initialize($this->container);

        $this->assertInstanceOf(GenericConsoleOutput::class, $consoleOutput);
    }

    /** @test */
    public function test_in_http_application()
    {
        $initializer = new ConsoleOutputInitializer();

        $this->container->singleton(Application::class, fn () => new HttpApplication(
            $this->container,
            $this->container->get(AppConfig::class),
        ));

        $consoleOutput = $initializer->initialize($this->container);

        $this->assertInstanceOf(NullConsoleOutput::class, $consoleOutput);
    }
}
