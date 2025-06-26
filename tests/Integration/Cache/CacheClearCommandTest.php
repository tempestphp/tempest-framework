<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Cache;

use Tempest\Cache\Commands\CacheClearCommand;
use Tempest\Cache\Config\InMemoryCacheConfig;
use Tempest\Core\DiscoveryCache;
use Tempest\Icon\IconCache;
use Tempest\View\ViewCache;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class CacheClearCommandTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->cache->fake();
    }

    public function test_cache_clear_default(): void
    {
        $this->console
            ->call(CacheClearCommand::class)
            ->assertSeeCount('CLEARED', expectedCount: 1);
    }

    public function test_cache_clear_default_named(): void
    {
        $this->console
            ->call(CacheClearCommand::class, ['tag' => 'default'])
            ->assertSeeCount('CLEARED', expectedCount: 1);
    }

    public function test_cache_clear_named(): void
    {
        $this->container->config(new InMemoryCacheConfig(tag: 'my-cache'));

        $this->console
            ->call(CacheClearCommand::class, ['tag' => 'my-cache'])
            ->assertSee('my-cache')
            ->assertSeeCount('CLEARED', expectedCount: 1);
    }

    public function test_cache_clear_default_all(): void
    {
        $this->console
            ->call(CacheClearCommand::class, ['all' => true])
            ->assertSeeCount('CLEARED', expectedCount: 1);
    }

    public function test_cache_clear_all(): void
    {
        $this->container->config(new InMemoryCacheConfig(tag: 'my-cache'));

        $this->console
            ->call(CacheClearCommand::class, ['all' => true])
            ->assertSee('default')
            ->assertSee('my-cache')
            ->assertSeeCount('CLEARED', expectedCount: 2);
    }

    public function test_cache_clear_filter(): void
    {
        $this->container->config(new InMemoryCacheConfig(tag: 'my-cache'));

        $this->console
            ->call(CacheClearCommand::class)
            ->submit('0')
            ->submit('yes')
            ->assertSee('default')
            ->assertNotSee('my-cache')
            ->assertSeeCount('CLEARED', expectedCount: 1);

        $this->console
            ->call(CacheClearCommand::class)
            ->submit('1')
            ->submit('yes')
            ->assertNotSee('default')
            ->assertSee('my-cache')
            ->assertSeeCount('CLEARED', expectedCount: 1);

        $this->console
            ->call(CacheClearCommand::class)
            ->submit('0,1')
            ->submit('yes')
            ->assertSee('default')
            ->assertSee('my-cache')
            ->assertSeeCount('CLEARED', expectedCount: 2);
    }
}
