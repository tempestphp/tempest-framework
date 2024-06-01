<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Console\Middleware;

use Tests\Tempest\Unit\Console\ConsoleIntegrationTestCase;

/**
 * @internal
 * @small
 */
class ForceMiddlewareTest extends ConsoleIntegrationTestCase
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
