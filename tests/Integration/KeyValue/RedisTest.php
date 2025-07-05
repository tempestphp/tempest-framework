<?php

namespace Tests\Tempest\Integration\KeyValue;

use Tempest\KeyValue\Redis\Config\RedisConfig;
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

        $this->container->config(new RedisConfig(
            prefix: 'tempest_test:',
            database: 6,
            connectionTimeOut: .2,
        ));

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

    public function test_command_is_dispatched(): void
    {
        $this->assertSame('response', $this->redis->command('PING', 'response'));

        $this->eventBus->assertDispatched(RedisCommandExecuted::class, function (RedisCommandExecuted $event): void {
            $this->assertSame('PING', $event->command);
            $this->assertSame(['response'], $event->arguments);
            $this->assertLessThanOrEqual(200, $event->duration->getTotalMilliseconds());
            $this->assertSame('response', $event->result);
        });
    }
}
