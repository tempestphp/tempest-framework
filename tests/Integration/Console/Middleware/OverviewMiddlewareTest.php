<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Middleware;

use PHPUnit\Framework\Attributes\Test;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class OverviewMiddlewareTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function overview(): void
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

    #[Test]
    public function overview_with_hidden(): void
    {
        $this->console
            ->call('', ['-a'])
            ->assertContains('hidden');

        $this->console
            ->call('', ['--all'])
            ->assertContains('hidden');
    }
}
