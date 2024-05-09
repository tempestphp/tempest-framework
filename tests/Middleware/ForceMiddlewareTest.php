<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Middleware;

use Tests\Tempest\Console\TestCase;

/**
 * @internal
 * @small
 */
class ForceMiddlewareTest extends TestCase
{
    public function test_force(): void
    {
        $this->console
            ->call('forcecommand --force')
            ->assertContains('continued');
    }

    public function test_force_flag(): void
    {
        $this->console
            ->call('forcecommand -f')
            ->assertContains('continued');
    }
}
