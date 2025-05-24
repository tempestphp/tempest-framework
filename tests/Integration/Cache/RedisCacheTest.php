<?php

namespace Tests\Tempest\Integration\Cache;

use Predis;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Tempest\Cache\GenericCache;
use Tempest\KeyValue\Redis\Config\RedisConfig;
use Tempest\KeyValue\Redis\PhpRedisClient;
use Tempest\KeyValue\Redis\PredisClient;
use Tempest\KeyValue\Redis\Redis;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class RedisCacheTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Redis is not configured on Windows CI.');
        }
    }

    public function test_php_redis_cache(): void
    {
        if (! extension_loaded('redis') || ! class_exists(\Redis::class)) {
            $this->markTestSkipped('The `redis` extension is not loaded.');
        }

        $redis = new PhpRedisClient(
            client: new \Redis(),
            config: new RedisConfig(
                prefix: 'tempest_test:',
                database: 6,
                connectionTimeOut: .2,
            ),
        );

        $cache = new GenericCache(new RedisAdapter($redis->getClient()));

        $cache->put('key', 'value');

        $this->assertSame('value', $cache->get('key'));
        $this->assertSame('s:5:"value";', $redis->get('tempest_test:key'));
    }

    public function test_predis_cache(): void
    {
        if (! class_exists(Predis\Client::class)) {
            $this->markTestSkipped('The `predis/predis` package is not installed.');
        }

        $redis = new PredisClient(new Predis\Client(
            parameters: [
                'database' => 6,
                'timeout' => .2,
            ],
            options: ['prefix' => 'tempest_test:'],
        ));

        $cache = new GenericCache(new RedisAdapter($redis->getClient()));

        $cache->put('key', 'value');

        $this->assertSame('value', $cache->get('key'));
        $this->assertSame('s:5:"value";', $redis->get('tempest_test:key'));
    }
}
