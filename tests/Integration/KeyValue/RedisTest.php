<?php

namespace Tests\Tempest\Integration\KeyValue;

use PHPUnit\Framework\Attributes\PostCondition;
use PHPUnit\Framework\Attributes\PreCondition;
use Tempest\KeyValue\Redis\Config\RedisConfig;
use Tempest\KeyValue\Redis\Redis;
use Tempest\KeyValue\Redis\RedisCommandExecuted;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Throwable;

final class RedisTest extends FrameworkIntegrationTestCase
{
    private Redis $redis;

    #[PreCondition]
    protected function configure(): void
    {
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

    #[PostCondition]
    protected function cleanup(): void
    {
        try {
            $this->redis->flush();
        } catch (Throwable) { // @mago-expect lint:no-empty-catch-clause
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
