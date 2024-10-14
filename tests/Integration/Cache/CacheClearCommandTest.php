<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Cache;

use Tempest\Cache\ProjectCache;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class CacheClearCommandTest extends FrameworkIntegrationTestCase
{
    public function test_cache_clear(): void
    {
        $this->console
            ->call('cache:clear')
            ->assertSee(ProjectCache::class)
            ->submit('0,1')
            ->submit('yes')
            ->assertSee('Tempest\Cache\ProjectCache cleared successfully')
            ->assertSee('Tempest\Core\DiscoveryCache cleared successfully')
            ->assertNotSee('Tempest\View\ViewCache cleared successfully')
            ->assertSee('Done');
    }

    public function test_cache_clear_all(): void
    {
        $this->console
            ->call('cache:clear --all')
            ->assertSee('Tempest\Cache\ProjectCache cleared successfully')
            ->assertSee('Tempest\Core\DiscoveryCache cleared successfully')
            ->assertSee('Tempest\View\ViewCache cleared successfully')
            ->assertSee('Done');
    }
}
