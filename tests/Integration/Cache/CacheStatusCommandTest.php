<?php

namespace Tests\Tempest\Integration\Cache;

use Tempest\Cache\Cache;
use Tempest\Cache\Commands\CacheStatusCommand;
use Tempest\Cache\Config\InMemoryCacheConfig;
use Tempest\Core\DiscoveryCache;
use Tempest\View\IconCache;
use Tempest\View\ViewCache;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class CacheStatusCommandTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->cache->fake();
    }

    public function test_cache_status(): void
    {
        $this->console
            ->call(CacheStatusCommand::class, ['internal' => false])
            ->assertSeeCount('ENABLED', expectedCount: 1);
    }

    public function test_cache_status_when_disabled(): void
    {
        $cache = $this->container->get(Cache::class);
        $cache->enabled = false;

        $this->console
            ->call(CacheStatusCommand::class, ['internal' => false])
            ->assertSeeCount('DISABLED', expectedCount: 1);
    }

    public function test_cache_status_with_multiple_caches(): void
    {
        $this->container->config(new InMemoryCacheConfig(tag: 'test-cache'));

        $this->console
            ->call(CacheStatusCommand::class, ['internal' => false])
            ->assertSee('test-cache')
            ->assertSeeCount('ENABLED', expectedCount: 2);
    }

    public function test_with_internal_caches(): void
    {
        $this->console
            ->call(CacheStatusCommand::class, ['internal' => true])
            ->assertSee(ViewCache::class)
            ->assertSee(IconCache::class)
            ->assertSee(DiscoveryCache::class);
    }
}
