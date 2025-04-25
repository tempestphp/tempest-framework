<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Middleware;

use PHPUnit\Framework\Attributes\CoversNothing;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
#[CoversNothing]
final class OverviewMiddlewareTest extends FrameworkIntegrationTestCase
{
    public function test_overview(): void
    {
        $this->console
            ->call('')
            ->assertContains('TEMPEST')
            ->assertContains('GENERAL')
            ->assertContains('HELLO')
            ->assertDoesNotContain('hidden')
            ->assertContains('hello:world')
            ->assertContains('hello:test   description')
            ->assertContains('hello:world:test')
            ->assertContains('test:test');
    }

    public function test_overview_with_hidden(): void
    {
        $this->console
            ->call('', ['-a'])
            ->assertContains('hidden');

        $this->console
            ->call('', ['--all'])
            ->assertContains('hidden');
    }
}
