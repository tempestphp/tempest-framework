<?php

declare(strict_types=1);

namespace Tempest\Cache\Tests\Integration;

use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Tempest\Cache\CacheConfig;
use Tempest\Cache\ProjectCache;
use Tempest\Clock\MockClock;
use Tempest\DateTime\Duration;
use Tempest\Drift\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class CacheTest extends FrameworkIntegrationTestCase
{
    public function test_put(): void
    {
        $clock = new MockClock();
        $pool = new ArrayAdapter(clock: $clock->toPsrClock());
        $cache = new ProjectCache(new CacheConfig(projectCachePool: $pool, enable: true));
        $interval = Duration::days(1);

        $cache->put('a', 'a', $clock->now()->plus($interval));
        $cache->put('b', 'b');

        $item = $pool->getItem('a');
        $this->assertTrue($item->isHit());
        $this->assertSame('a', $item->get());

        $clock->addInterval($interval);

        $item = $pool->getItem('a');
        $this->assertFalse($item->isHit());
        $this->assertSame(null, $item->get());

        $item = $pool->getItem('b');
        $this->assertTrue($item->isHit());
    }

    public function test_get(): void
    {
        $clock = new MockClock();
        $pool = new ArrayAdapter(clock: $clock->toPsrClock());
        $cache = new ProjectCache(new CacheConfig(projectCachePool: $pool, enable: true));
        $interval = Duration::days(1);

        $cache->put('a', 'a', $clock->now()->plus($interval));
        $cache->put('b', 'b');

        $this->assertSame('a', $cache->get('a'));
        $this->assertSame('b', $cache->get('b'));

        $clock->addInterval($interval);

        $this->assertSame(null, $cache->get('a'));
        $this->assertSame('b', $cache->get('b'));
    }

    public function test_resolve(): void
    {
        $clock = new MockClock();
        $pool = new ArrayAdapter(clock: $clock->toPsrClock());
        $config = new CacheConfig(
            projectCachePool: $pool,
            enable: true,
        );
        $cache = new ProjectCache($config);
        $interval = Duration::days(1);

        $a = $cache->resolve('a', fn () => 'a', $clock->now()->plus($interval));
        $this->assertSame('a', $a);

        $b = $cache->resolve('b', fn () => 'b');
        $this->assertSame('b', $b);

        $clock->addInterval($interval);

        $this->assertSame(null, $cache->get('a'));
        $this->assertSame('b', $cache->get('b'));

        $b = $cache->resolve('b', fn () => 'b');
        $this->assertSame('b', $b);
    }

    public function test_remove(): void
    {
        $pool = new ArrayAdapter();
        $cache = new ProjectCache(new CacheConfig(projectCachePool: $pool, enable: true));

        $cache->put('a', 'a');

        $cache->remove('a');

        $this->assertNull($cache->get('a'));
    }

    public function test_clear(): void
    {
        $pool = new ArrayAdapter();
        $cache = new ProjectCache(cacheConfig: new CacheConfig(projectCachePool: $pool));

        $cache->put('a', 'a');
        $cache->put('b', 'b');

        $cache->clear();

        $this->assertNull($cache->get('a'));
        $this->assertNull($cache->get('b'));
    }
}
