<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Cache;

use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Tempest\Cache\GenericCache;
use Tempest\Cache\NotNumberException;
use Tempest\Core\DeferredTasks;
use Tempest\Core\Kernel\FinishDeferredTasks;
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
        $cache = new GenericCache($pool = new ArrayAdapter());

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
        $cache = new GenericCache(new ArrayAdapter());

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

        $cache = new GenericCache(new ArrayAdapter());

        $cache->put('a', 'value');
        $cache->increment('a', by: 1);
    }

    public function test_increment_non_int_numeric_key(): void
    {
        $cache = new GenericCache(new ArrayAdapter());

        $cache->put('a', '1');

        $cache->increment('a', by: 1);
        $this->assertSame(2, $cache->get('a'));
    }

    public function test_decrement_non_int_key(): void
    {
        $this->expectException(NotNumberException::class);

        $cache = new GenericCache(new ArrayAdapter());

        $cache->put('a', 'value');
        $cache->decrement('a', by: 1);
    }

    public function test_decrement_non_int_numeric_key(): void
    {
        $cache = new GenericCache(new ArrayAdapter());

        $cache->put('a', '1');

        $cache->decrement('a', by: 1);
        $this->assertSame(0, $cache->get('a'));
    }

    public function test_decrement(): void
    {
        $cache = new GenericCache(new ArrayAdapter());

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
        $cache = new GenericCache(new ArrayAdapter(clock: $clock->toPsrClock()));

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
        $cache = new GenericCache(new ArrayAdapter());

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
        $cache = new GenericCache(new ArrayAdapter(clock: $clock->toPsrClock()));

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
        $cache = new GenericCache(new ArrayAdapter());

        $cache->put('a', 'a');
        $cache->remove('a');

        $this->assertNull($cache->get('a'));
    }

    public function test_clear(): void
    {
        $cache = new GenericCache(new ArrayAdapter());

        $cache->put('a', 'a');
        $cache->put('b', 'b');

        $cache->clear();

        $this->assertNull($cache->get('a'));
        $this->assertNull($cache->get('b'));
    }

    public function test_stale_while_revalidate(): void
    {
        $clock = $this->clock();
        $cache = new GenericCache(
            adapter: $pool = new ArrayAdapter(clock: $clock->toPsrClock()),
            deferredTasks: $tasks = $this->container->get(DeferredTasks::class),
        );

        // Cache value can be stale for 1min, but will be refreshed in the background
        $retrieve = fn (string $value) => $cache->resolve('test', fn () => $value, expiration: Duration::minute(), stale: Duration::minute());

        // We fetch the value within the allowed duration, there is no deferring
        $this->assertSame('update1', $retrieve('update1'));
        $this->assertSame('update1', $pool->getItem('test')->get());
        $this->assertSame($clock->now()->plus(Duration::minute())->getTimestamp()->getSeconds(), $pool->getItem('tempest.stale-cache.stale-at.test')->get());
        $this->assertEmpty($tasks->getTasks());

        // After 30 seconds, we should still get the same value, with no plan for refreshing it
        $this->container->invoke(FinishDeferredTasks::class);
        $clock->plus(Duration::seconds(30));
        $this->assertSame('update1', $retrieve('update2'));
        $this->assertSame($clock->now()->plus(Duration::seconds(30))->getTimestamp()->getSeconds(), $pool->getItem('tempest.stale-cache.stale-at.test')->get());
        $this->assertEmpty($tasks->getTasks());

        // We fetch it again after one minute, within stale window, so under the hood it gets deferred for refresh
        $this->container->invoke(FinishDeferredTasks::class);
        $clock->plus(Duration::seconds(30));
        $this->assertSame('update1', $retrieve('update3'));
        $this->assertSame($clock->now()->getTimestamp()->getSeconds(), $pool->getItem('tempest.stale-cache.stale-at.test')->get());
        $this->assertCount(1, $tasks->getTasks());

        // After 1min30 total, within stale window again, we should get the previous value,
        // since it has been refreshed by the deferred task. However, we start the countdown
        // again, so we are within the fresh window. So, no update task will be deferred.
        $this->container->invoke(FinishDeferredTasks::class);
        $clock->plus(Duration::seconds(30));
        $this->assertSame('update3', $retrieve('update4'));
        $this->assertSame($clock->now()->plus(Duration::seconds(30))->getTimestamp()->getSeconds(), $pool->getItem('tempest.stale-cache.stale-at.test')->get());
        $this->assertEmpty($tasks->getTasks());

        // We now try it again 2 minutes after last refresh, the value is
        // totally invalidated, with no plan on refreshing it yet since we just did.
        $this->container->invoke(FinishDeferredTasks::class);
        $clock->plus(Duration::minutes(2));
        $this->assertSame('update5', $retrieve('update5'));
        $this->assertSame($clock->now()->plus(Duration::seconds(60))->getTimestamp()->getSeconds(), $pool->getItem('tempest.stale-cache.stale-at.test')->get());
        $this->assertEmpty($tasks->getTasks());
    }
}
