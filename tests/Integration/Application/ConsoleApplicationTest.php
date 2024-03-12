<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Application;

use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
class ConsoleApplicationTest extends FrameworkIntegrationTestCase
{
    public function test_unhandled_command()
    {
        $this->console
            ->call('unknown')
            ->assertContains('Command `unknown` not found');
    }

    public function test_cli_application()
    {
        $this->console
            ->call('hello:world input')
            ->assertContains('Hi')
            ->assertContains('input');
    }

    public function test_cli_application_flags()
    {
        $this->console
            ->call('hello:test --flag --optionalValue=1')
            ->assertContains('1')
            ->assertContains('flag');
    }

    public function test_cli_application_flags_defaults()
    {
        $this->console
            ->call('hello:test')
            ->assertContains('null')
            ->assertContains('no-flag');
    }

    public function test_failing_command()
    {
        $this->console
            ->call('hello:world')
            ->assertContains('Something went wrong');
    }
}
