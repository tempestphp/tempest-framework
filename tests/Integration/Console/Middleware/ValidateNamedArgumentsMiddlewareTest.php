<?php

namespace Integration\Console\Middleware;

use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class ValidateNamedArgumentsMiddlewareTest extends FrameworkIntegrationTestCase
{
    public function test_invalid_parameters_throw_exception(): void
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
}
