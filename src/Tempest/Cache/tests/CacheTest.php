<?php

namespace Tempest\Cache\Tests;

use DateInterval;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Tempest\Cache\Cache;
use Tempest\Clock\MockClock;

final class CacheTest extends TestCase
{
    public function test_put(): void
    {
        $clock = new MockClock();
        $pool = new ArrayAdapter(clock: $clock);
        $cache = new Cache($pool);
        $interval = new DateInterval('P1D');

        $cache->put('a', 'a', $clock->now()->add($interval));

        $item = $pool->getItem('a');
        $this->assertTrue($item->isHit());
        $this->assertSame('a', $item->get());

        $clock->addInterval($interval);

        $item = $pool->getItem('a');
        $this->assertFalse($item->isHit());
        $this->assertSame(null, $item->get());
    }

    public function test_get(): void
    {
        $clock = new MockClock();
        $pool = new ArrayAdapter(clock: $clock);
        $cache = new Cache($pool);
        $interval = new DateInterval('P1D');

        $cache->put('a', 'a', $clock->now()->add($interval));

        $item = $pool->getItem('a');
        $this->assertTrue($item->isHit());
        $this->assertSame('a', $item->get());

        $clock->addInterval($interval);
        $value = $cache->get('a', fn () => 'a');
    }
}