<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Cache;

use DateInterval;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Tempest\Cache\CacheConfig;
use Tempest\Cache\ProjectCache;
use Tempest\Clock\MockClock;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
#[CoversNothing]
final class CacheTest extends FrameworkIntegrationTestCase
{
    public function test_put(): void
    {
        $clock = new MockClock();
        $pool = new ArrayAdapter(clock: $clock);
        $cache = new ProjectCache(new CacheConfig(projectCachePool: $pool, enable: true));
        $interval = new DateInterval('P1D');

        $cache->put('a', 'a', $clock->now()->add($interval));
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
        $pool = new ArrayAdapter(clock: $clock);
        $cache = new ProjectCache(new CacheConfig(projectCachePool: $pool, enable: true));
        $interval = new DateInterval('P1D');

        $cache->put('a', 'a', $clock->now()->add($interval));
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
        $pool = new ArrayAdapter(clock: $clock);
        $config = new CacheConfig(
            projectCachePool: $pool,
            enable: true,
        );
        $cache = new ProjectCache($config);
        $interval = new DateInterval('P1D');

        $a = $cache->resolve('a', fn () => 'a', $clock->now()->add($interval));
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
