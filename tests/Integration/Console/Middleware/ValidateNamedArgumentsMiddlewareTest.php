<?php

namespace Tests\Tempest\Integration\Console\Middleware;

use PHPUnit\Framework\Attributes\Test;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class ValidateNamedArgumentsMiddlewareTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function invalid_parameters_throw_exception(): void
    {
        $this->console
            ->call('test:flags --unknown --foo --no-flag --help --force --no-interaction')
            ->assertError()
            ->assertContains('unknown')
            ->assertDoesNotContain('foo')
            ->assertDoesNotContain('flag')
            ->assertDoesNotContain('force')
            ->assertDoesNotContain('help')
            ->assertDoesNotContain('interaction');
    }

    #[Test]
    public function command_with_dynamic_parameters(): void
    {
        $this->console
            ->call('dynamic:params --dynamic')
            ->assertContains('yes');

        $this->console
            ->call('dynamic:params')
            ->assertContains('no');
    }
}
