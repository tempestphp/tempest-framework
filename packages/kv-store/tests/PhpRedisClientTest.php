<?php

namespace Tempest\KeyValue\Tests;

use PHPUnit\Framework\Attributes\PostCondition;
use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;
use Tempest\KeyValue\Redis\Config\RedisConfig;
use Tempest\KeyValue\Redis\PhpRedisClient;
use Throwable;

#[RequiresPhpExtension('redis')]
final class PhpRedisClientTest extends TestCase
{
    private PhpRedisClient $redis;

    #[PreCondition]
    protected function configure(): void
    {
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
        } catch (Throwable) {
            $this->markTestSkipped('Could not connect to Redis.');
        }
    }

    #[PostCondition]
    protected function cleanup(): void
    {
        try {
            $this->redis->flush();
        } catch (Throwable) {
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
