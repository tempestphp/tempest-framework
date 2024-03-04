<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Application;

use Tempest\Testing\IntegrationTest;

class ConsoleApplicationTest extends IntegrationTest
{
    /** @test */
    public function test_unhandled_command()
    {
        $this->console
            ->call('unknown')
            ->assertContains('Command `unknown` not found');
    }

    /** @test */
    public function test_cli_application()
    {
        $this->console
            ->call('hello:world input')
            ->assertContains('Hi')
            ->assertContains('input');
    }

    /** @test */
    public function test_cli_application_flags()
    {
        $this->console
            ->call('hello:test --flag --optionalValue=1')
            ->assertContains('1')
            ->assertContains('flag');
    }

    /** @test */
    public function test_cli_application_flags_defaults()
    {
        $this->console
            ->call('hello:test')
            ->assertContains('null')
            ->assertContains('no-flag');
    }

    /** @test */
    public function test_failing_command()
    {
        $this->console
            ->call('hello:world')
            ->assertContains('Something went wrong');
    }
}
