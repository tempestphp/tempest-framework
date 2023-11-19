<?php

declare(strict_types=1);

namespace Tests\Tempest\Application;

use Tests\Tempest\TestCase;

class ConsoleApplicationTest extends TestCase
{
    /** @test */
    public function test_cli_application()
    {
        $output = $this->console('hello:world input');

        $this->assertSame(
            ['Hi', 'input'],
            $output->lines,
        );
    }

    /** @test */
    public function test_cli_application_flags()
    {
        $output = $this->console('hello:test --flag --optionalValue=1');

        $this->assertSame(
            ['1', 'flag'],
            $output->lines,
        );
    }

    /** @test */
    public function test_cli_application_flags_defaults()
    {
        $output = $this->console('hello:test');

        $this->assertSame(
            ['null', 'no-flag'],
            $output->lines,
        );
    }

    /** @test */
    public function test_failing_command()
    {
        $output = $this->console('hello:world');

        $this->assertSame(
            ['Something went wrong'],
            $output->lines,
        );
    }
}
