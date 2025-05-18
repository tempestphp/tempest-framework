<?php

namespace Tests\Tempest\Integration\KeyValue;

use Tempest\KeyValue\Redis\Config\PhpRedisConfig;
use Tempest\KeyValue\Redis\Config\PredisConfig;
use Tempest\KeyValue\Redis\Redis;
use Tempest\KeyValue\Redis\RedisCommandExecuted;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

final class RedisTest extends FrameworkIntegrationTestCase
{
    private Redis $redis;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventBus->preventEventHandling();

        if (extension_loaded('redis')) {
            $this->container->config(new PhpRedisConfig(
                prefix: 'tempest_test:',
                database: 6,
                connectionTimeOut: .2,
            ));
        } else {
            $this->container->config(new PredisConfig(
                prefix: 'tempest_test:',
                database: 6,
                connectionTimeOut: .2,
            ));
        }

        $this->redis = $this->container->get(Redis::class);

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

    public function test_command(): void
    {
        $this->assertSame('response', $this->redis->command('PING', 'response'));

        $this->eventBus->assertDispatched(RedisCommandExecuted::class, function (RedisCommandExecuted $event): void {
            $this->assertSame('PING', $event->command);
            $this->assertSame(['response'], $event->arguments);
            $this->assertLessThanOrEqual(200, $event->duration->getTotalMilliseconds());
            $this->assertSame('response', $event->result);
        });
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
