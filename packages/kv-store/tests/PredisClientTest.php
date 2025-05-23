<?php

namespace Tempest\KeyValue\Tests;

use PHPUnit\Framework\TestCase;
use Predis;
use Tempest\KeyValue\Redis\PhpRedisClient;
use Tempest\KeyValue\Redis\PredisClient;

final class PredisClientTest extends TestCase
{
    private PredisClient $redis;

    protected function setUp(): void
    {
        parent::setUp();

        if (! class_exists(Predis\Client::class)) {
            $this->markTestSkipped('The `predis/predis` package is not installed.');
        }

        $this->redis = new PredisClient(
            client: new Predis\Client(
                parameters: array_filter([
                    'scheme' => 'tcp',
                    'host' => '127.0.0.1',
                    'port' => 6379,
                    'database' => 6,
                    'timeout' => .2,
                ]),
                options: ['prefix' => 'tempest_test:'],
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
        $this->assertInstanceOf(Predis\Client::class, $this->redis->getClient());
    }

    public function test_set(): void
    {
        $this->redis->set('key_string', 'my-value');
        $this->redis->set('key_array_associative', ['foo' => 'bar']);
        $this->redis->set('key_array_list', ['foo', 'bar']);

        $this->assertSame('my-value', $this->redis->getClient()->executeRaw(['GET', 'key_string']));
        $this->assertSame('{"foo":"bar"}', $this->redis->getClient()->executeRaw(['GET', 'key_array_associative']));
        $this->assertSame('["foo","bar"]', $this->redis->getClient()->executeRaw(['GET', 'key_array_list']));
    }

    public function test_get(): void
    {
        $this->redis->getClient()->executeRaw(['SET', 'key_string', 'my_value']);
        $this->redis->getClient()->executeRaw(['SET', 'key_array_associative', '{"foo":"bar"}']);
        $this->redis->getClient()->executeRaw(['SET', 'key_array_list', '["foo","bar"]']);

        $this->assertSame('my_value', $this->redis->get('key_string'));
        $this->assertSame(['foo' => 'bar'], $this->redis->get('key_array_associative'));
        $this->assertSame(['foo', 'bar'], $this->redis->get('key_array_list'));
    }
}
