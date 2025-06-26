<?php

namespace Tests\Tempest\Integration\Cache;

use PHPUnit\Framework\ExpectationFailedException;
use Tempest\Cache\Cache;
use Tempest\Cache\Config\InMemoryCacheConfig;
use Tempest\Cache\CacheUsageWasForbidden;
use Tempest\Cache\Testing\TestingCache;
use Tempest\DateTime\Duration;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class CacheTesterTest extends FrameworkIntegrationTestCase
{
    public function test_fake_cache_is_registered_in_container(): void
    {
        $faked = $this->cache->fake();
        $actual = $this->container->get(Cache::class);

        $this->assertInstanceOf(TestingCache::class, $faked);
        $this->assertInstanceOf(TestingCache::class, $actual);
        $this->assertSame($faked, $actual);
    }

    public function test_multiple_fake_cache_are_registered_in_container(): void
    {
        $faked1 = $this->cache->fake('cache1');
        $faked2 = $this->cache->fake('cache2');

        $actual1 = $this->container->get(Cache::class, 'cache1');
        $actual2 = $this->container->get(Cache::class, 'cache2');

        $this->assertInstanceOf(TestingCache::class, $faked1);
        $this->assertInstanceOf(TestingCache::class, $actual1);
        $this->assertSame($faked1, $actual1);

        $this->assertInstanceOf(TestingCache::class, $faked2);
        $this->assertInstanceOf(TestingCache::class, $actual2);
        $this->assertSame($faked2, $actual2);

        $this->assertNotSame($actual1, $actual2);
    }

    public function test_basic_assertions(): void
    {
        $cache = $this->cache->fake();

        $cache->assertEmpty();

        $cache->put('key', 'value');

        $cache->assertCached('key');
        $cache->assertCached('key', function (string $value): void {
            $this->assertSame('value', $value);
        });

        $cache->assertNotCached('foo');
        $cache->assertNotEmpty();

        $cache->assertKeyHasValue('key', 'value');
        $cache->assertKeyDoesNotHaveValue('key', 'not-the-right-value');
    }

    public function test_prevent_usage_without_fake(): void
    {
        $this->expectException(CacheUsageWasForbidden::class);

        $this->cache->preventUsageWithoutFake();

        $cache = $this->container->get(Cache::class);
        $cache->put('key', 'value');
    }

    public function test_prevent_usage_without_fake_with_tagged_cache(): void
    {
        $this->expectException(CacheUsageWasForbidden::class);

        $this->container->config(new InMemoryCacheConfig(tag: 'tagged'));
        $this->cache->preventUsageWithoutFake();

        $cache = $this->container->get(Cache::class, 'tagged');
        $cache->put('key', 'value');
    }

    public function test_prevent_usage_without_fake_with_fake(): void
    {
        $this->cache->preventUsageWithoutFake();

        $cache = $this->cache->fake();
        $cache->put('key', 'value');
        $cache->assertCached('key');
    }

    public function test_prevent_usage_without_fake_with_fake_tagged_cache(): void
    {
        $this->container->config(new InMemoryCacheConfig(tag: 'tagged'));
        $this->cache->preventUsageWithoutFake();

        $cache = $this->cache->fake('tagged');
        $cache->put('key', 'value');
        $cache->assertCached('key');
    }

    public function test_ttl(): void
    {
        $clock = $this->clock();
        $cache = $this->cache->fake();

        $cache->put('key', 'value', expiration: Duration::minutes(10));
        $cache->assertCached('key');

        $clock->plus(Duration::minutes(10)->withSeconds(1));
        $cache->assertNotCached('key');
    }

    public function test_lock_assertions(): void
    {
        $cache = $this->cache->fake();
        $lock = $cache->lock('processing');

        $lock->assertNotLocked();

        $this->assertTrue($lock->acquire());

        $lock->assertLocked();
        $lock->assertLocked(by: $lock->owner);

        $lock->assertNotLocked(by: 'other-owner');

        $cache->assertLocked($lock->key);
    }

    public function test_assert_not_locked_while_locked(): void
    {
        $cache = $this->cache->fake();
        $lock = $cache->lock('processing');

        $this->assertTrue($lock->acquire());

        $this->expectException(ExpectationFailedException::class);
        $lock->assertNotLocked();
    }

    public function test_lock_assertion_with_different_owner(): void
    {
        $testingCache = $this->cache->fake();

        $actualCache = $this->container->get(Cache::class);
        $actualCache->lock('processing')->acquire();

        $testingCache->assertLocked('processing');
    }
}
