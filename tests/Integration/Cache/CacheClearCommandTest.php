<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Cache;

use PHPUnit\Framework\Attributes\CoversNothing;
use Tempest\Cache\ProjectCache;
use Tempest\View\ViewCache;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
#[CoversNothing]
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
