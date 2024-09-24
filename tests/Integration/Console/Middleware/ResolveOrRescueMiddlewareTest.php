<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Middleware;

use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class ResolveOrRescueMiddlewareTest extends FrameworkIntegrationTestCase
{
    public function test_finds_single_similar_command(): void
    {
        $this->console
            ->call('discovery:sta')
            ->assertSee('Did you mean discovery:status?');

        $this->console
            ->call('bascovery:status')
            ->assertSee('Did you mean discovery:status?');
    }

    public function test_finds_multiple_similar_commands(): void
    {
        $this->console
            ->call('discovery')
            ->assertSee('Did you mean to run one of these?  [discovery:status/discovery:clear]');
    }
}
