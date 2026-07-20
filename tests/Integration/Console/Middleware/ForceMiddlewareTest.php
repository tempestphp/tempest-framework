<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Middleware;

use PHPUnit\Framework\Attributes\Test;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class ForceMiddlewareTest extends FrameworkIntegrationTestCase
{
    #[Test]
    public function force(): void
    {
        $this->console
            ->call('force --force')
            ->assertContains('continued');
    }

    #[Test]
    public function force_flag(): void
    {
        $this->console
            ->call('force -f')
            ->assertContains('continued');
    }
}
