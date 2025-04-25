<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Middleware;

use PHPUnit\Framework\Attributes\CoversNothing;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
#[CoversNothing]
final class ForceMiddlewareTest extends FrameworkIntegrationTestCase
{
    public function test_force(): void
    {
        $this->console
            ->call('force --force')
            ->assertContains('continued');
    }

    public function test_force_flag(): void
    {
        $this->console
            ->call('force -f')
            ->assertContains('continued');
    }
}
