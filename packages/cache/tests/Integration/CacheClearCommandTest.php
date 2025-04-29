<?php

declare(strict_types=1);

namespace Tempest\Cache\Tests\Integration;

use Tempest\Cache\ProjectCache;
use Tempest\Drift\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class CacheClearCommandTest extends FrameworkIntegrationTestCase
{
    public function test_cache_clear_single(): void
    {
        $this->console
            ->call('cache:clear')
            ->assertSee(ProjectCache::class)
            ->submit('0')
            ->submit('yes')
            ->assertSeeCount('CLEARED', 1);
    }
}
