<?php

declare(strict_types=1);

namespace Tests\Tempest\Application;

use Tempest\AppConfig;
use Tempest\Application\CommandNotFound;
use Tempest\Application\ConsoleApplication;
use Tempest\Console\ConsoleOutput;
use Tests\Tempest\TestCase;

class ConsoleApplicationTest extends TestCase
{
    /** @test */
    public function test_run()
    {
        $app = new ConsoleApplication(
            ['hello:world input'],
            $this->container,
            $this->container->get(AppConfig::class),
        );

        $app->run();

        /** @var \Tests\Tempest\TestConsoleOutput $output */
        $output = $this->container->get(ConsoleOutput::class);

        $this->assertStringContainsString('Tempest Console', $output->lines[0]);
    }

    /** @test */
    public function test_unhandled_command()
    {
        $this->expectException(CommandNotFound::class);

        $this->console('unknown');
    }

    /** @test */
    public function test_cli_application()
    {
        $output = $this->console('hello:world input');

        $this->assertSame(
            ['Hi', 'input'],
            $output->getLinesWithoutFormatting(),
        );
    }

    /** @test */
    public function test_cli_application_flags()
    {
        $output = $this->console('hello:test --flag --optionalValue=1');

        $this->assertSame(
            ['1', 'flag'],
            $output->getLinesWithoutFormatting(),
        );
    }

    /** @test */
    public function test_cli_application_flags_defaults()
    {
        $output = $this->console('hello:test');

        $this->assertSame(
            ['null', 'no-flag'],
            $output->getLinesWithoutFormatting(),
        );
    }

    /** @test */
    public function test_failing_command()
    {
        $output = $this->console('hello:world');

        $this->assertSame(
            ['Something went wrong'],
            $output->getLinesWithoutFormatting(),
        );
    }
}
