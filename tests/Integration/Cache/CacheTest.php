<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Cache;

use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Tempest\Cache\Config\InMemoryCacheConfig;
use Tempest\Cache\GenericCache;
use Tempest\Cache\NotNumberException;
use Tempest\DateTime\Duration;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Support\str;

/**
 * @internal
 */
final class CacheTest extends FrameworkIntegrationTestCase
{
    public function test_put(): void
    {
        $interval = Duration::days(1);
        $clock = $this->clock();
        $cache = new GenericCache(
            cacheConfig: new InMemoryCacheConfig(),
            adapter: $pool = new ArrayAdapter(clock: $clock->toPsrClock()),
        );

        $cache->put('a', 'a', $clock->now()->plus($interval));
        $cache->put('b', 'b');

        $item = $pool->getItem('a');
        $this->assertTrue($item->isHit());
        $this->assertSame('a', $item->get());

        $clock->plus($interval);

        $item = $pool->getItem('a');
        $this->assertFalse($item->isHit());
        $this->assertSame(null, $item->get());

        $item = $pool->getItem('b');
        $this->assertTrue($item->isHit());
    }

    public function test_put_many(): void
    {
        $cache = new GenericCache(
            cacheConfig: new InMemoryCacheConfig(),
            adapter: $pool = new ArrayAdapter(),
        );

        $cache->putMany(['foo1' => 'bar1', 'foo2' => 'bar2']);

        $item = $pool->getItem('foo1');
        $this->assertTrue($item->isHit());
        $this->assertSame('bar1', $item->get());
        $this->assertSame('bar1', $cache->get('foo1'));

        $item = $pool->getItem('foo2');
        $this->assertTrue($item->isHit());
        $this->assertSame('bar2', $item->get());
        $this->assertSame('bar2', $cache->get('foo2'));
    }

    public function test_increment(): void
    {
        $cache = new GenericCache(new InMemoryCacheConfig());

        $cache->increment('a', by: 1);
        $this->assertSame(1, $cache->get('a'));

        $cache->increment('a', by: 1);
        $this->assertSame(2, $cache->get('a'));

        $cache->increment('a', by: 5);
        $this->assertSame(7, $cache->get('a'));

        $cache->increment('a', by: -1);
        $this->assertSame(6, $cache->get('a'));

        $cache->increment('b', by: 1);
        $this->assertSame(1, $cache->get('b'));
    }

    public function test_increment_non_int_key(): void
    {
        $this->expectException(NotNumberException::class);

        $cache = new GenericCache(new InMemoryCacheConfig());

        $cache->put('a', 'value');
        $cache->increment('a', by: 1);
    }

    public function test_increment_non_int_numeric_key(): void
    {
        $cache = new GenericCache(new InMemoryCacheConfig());

        $cache->put('a', '1');

        $cache->increment('a', by: 1);
        $this->assertSame(2, $cache->get('a'));
    }

    public function test_decrement_non_int_key(): void
    {
        $this->expectException(NotNumberException::class);

        $cache = new GenericCache(new InMemoryCacheConfig());

        $cache->put('a', 'value');
        $cache->decrement('a', by: 1);
    }

    public function test_decrement_non_int_numeric_key(): void
    {
        $cache = new GenericCache(new InMemoryCacheConfig());

        $cache->put('a', '1');

        $cache->decrement('a', by: 1);
        $this->assertSame(0, $cache->get('a'));
    }

    public function test_decrement(): void
    {
        $cache = new GenericCache(new InMemoryCacheConfig());

        $cache->decrement('a', by: 1);
        $this->assertSame(-1, $cache->get('a'));

        $cache->decrement('a', by: 1);
        $this->assertSame(-2, $cache->get('a'));

        $cache->decrement('a', by: 5);
        $this->assertSame(-7, $cache->get('a'));

        $cache->decrement('a', by: -1);
        $this->assertSame(-6, $cache->get('a'));
    }

    public function test_get(): void
    {
        $interval = Duration::days(1);
        $clock = $this->clock();
        $cache = new GenericCache(
            cacheConfig: new InMemoryCacheConfig(),
            adapter: new ArrayAdapter(clock: $clock->toPsrClock()),
        );

        $cache->put('a', 'a', $clock->now()->plus($interval));
        $cache->put('b', 'b');

        $this->assertSame('a', $cache->get(str('a')));
        $this->assertSame('b', $cache->get('b'));

        $clock->plus($interval);

        $this->assertSame(null, $cache->get('a'));
        $this->assertSame('b', $cache->get('b'));
    }

    public function test_get_many(): void
    {
        $cache = new GenericCache(new InMemoryCacheConfig());

        $cache->put('foo1', 'bar1');
        $cache->put('foo2', 'bar2');

        $values = $cache->getMany(['foo1', 'foo2']);

        $this->assertSame('bar1', $values['foo1']);
        $this->assertSame('bar2', $values['foo2']);

        $values = $cache->getMany(['foo2', 'foo3']);

        $this->assertSame('bar2', $values['foo2']);
        $this->assertSame(null, $values['foo3']);
    }

    public function test_resolve(): void
    {
        $interval = Duration::days(1);
        $clock = $this->clock();
        $cache = new GenericCache(
            cacheConfig: new InMemoryCacheConfig(),
            adapter: new ArrayAdapter(clock: $clock->toPsrClock()),
        );

        $a = $cache->resolve('a', fn () => 'a', $clock->now()->plus($interval));
        $this->assertSame('a', $a);

        $b = $cache->resolve('b', fn () => 'b');
        $this->assertSame('b', $b);

        $clock->plus($interval);

        $this->assertSame(null, $cache->get('a'));
        $this->assertSame('b', $cache->get('b'));

        $b = $cache->resolve('b', fn () => 'b');
        $this->assertSame('b', $b);
    }

    public function test_remove(): void
    {
        $cache = new GenericCache(new InMemoryCacheConfig());

        $cache->put('a', 'a');
        $cache->remove('a');

        $this->assertNull($cache->get('a'));
    }

    public function test_clear(): void
    {
        $cache = new GenericCache(new InMemoryCacheConfig());

        $cache->put('a', 'a');
        $cache->put('b', 'b');

        $cache->clear();

        $this->assertNull($cache->get('a'));
        $this->assertNull($cache->get('b'));
    }
}
