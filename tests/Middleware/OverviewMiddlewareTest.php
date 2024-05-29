<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Middleware;

use Tests\Tempest\Console\TestCase;

/**
 * @internal
 * @small
 */
class OverviewMiddlewareTest extends TestCase
{
    public function test_overview(): void
    {
        $this->console
            ->call('')
            ->assertContains('Tempest')
            ->assertContains('General')
            ->assertContains('Hello')
            ->assertDoesNotContain('hidden')
            ->assertContains('hello:world <input>')
            ->assertContains('hello:test [optionalValue=null] [--flag=false] - description')
            ->assertContains('testcommand:test');
    }

    public function test_overview_with_hidden(): void
    {
        $this->console
            ->call('-a')
            ->assertContains('hidden');

        $this->console
            ->call('--all')
            ->assertContains('hidden');
    }
}
