<?php

namespace Tempest\KeyValue\Tests;

use PHPUnit\Framework\TestCase;
use Tempest\KeyValue\Redis\Config\RedisConfig;
use Tempest\KeyValue\Redis\PhpRedisClient;

final class PhpRedisClientTest extends TestCase
{
    private PhpRedisClient $redis;

    protected function setUp(): void
    {
        parent::setUp();

        $this->redis = new PhpRedisClient(
            client: new \Redis(),
            config: new RedisConfig(
                prefix: 'tempest_test:',
                database: 6,
                connectionTimeOut: .2,
            ),
        );

        try {
            $this->redis->connect();
        } catch (\Throwable) {
            $this->markTestSkipped('Could not connect to Redis.');
        }
    }

    protected function tearDown(): void
    {
        try {
            $this->redis->flush();
        } finally {
            parent::tearDown();
        }
    }

    public function test_basic(): void
    {
        $this->assertSame('response', $this->redis->command('PING', 'response'));
        $this->assertInstanceOf(\Redis::class, $this->redis->getClient());
    }

    public function test_set(): void
    {
        $this->redis->set('key_string', 'my-value');
        $this->redis->set('key_array_associative', ['foo' => 'bar']);
        $this->redis->set('key_array_list', ['foo', 'bar']);

        $this->assertSame('my-value', $this->redis->getClient()->rawcommand('GET', 'key_string'));
        $this->assertSame('{"foo":"bar"}', $this->redis->getClient()->rawcommand('GET', 'key_array_associative'));
        $this->assertSame('["foo","bar"]', $this->redis->getClient()->rawcommand('GET', 'key_array_list'));
    }

    public function test_get(): void
    {
        $this->redis->getClient()->rawcommand('SET', 'key_string', 'my_value');
        $this->redis->getClient()->rawcommand('SET', 'key_array_associative', '{"foo":"bar"}');
        $this->redis->getClient()->rawcommand('SET', 'key_array_list', '["foo","bar"]');

        $this->assertSame('my_value', $this->redis->get('key_string'));
        $this->assertSame(['foo' => 'bar'], $this->redis->get('key_array_associative'));
        $this->assertSame(['foo', 'bar'], $this->redis->get('key_array_list'));
    }
}
