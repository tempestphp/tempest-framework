<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\RateLimit;

use PHPUnit\Framework\Attributes\PostCondition;
use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;
use Tempest\DateTime\Duration;
use Tempest\KeyValue\Redis\Config\RedisConfig;
use Tempest\KeyValue\Redis\Redis;
use Tempest\RateLimit\Config\RedisRateLimitConfig;
use Tempest\RateLimit\RateLimitStorage;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Throwable;

/**
 * @internal
 */
final class RedisRateLimitStorageTest extends FrameworkIntegrationTestCase
{
    private Redis $redis;

    private RateLimitStorage $rateLimitStorage;

    #[PreCondition]
    protected function configure(): void
    {
        $this->eventBus->preventEventHandling();

        $this->container->config(new RedisConfig(
            prefix: 'tempest_test:',
            // Cleaning up flushes the database, so this suite keeps one to itself. The other Redis
            // suites share database 6, and in parallel they would flush each other's keys mid-test.
            database: 7,
            connectionTimeOut: .2,
        ));

        $this->redis = $this->container->get(Redis::class);

        try {
            $this->redis->connect();
        } catch (Throwable) {
            $this->markTestSkipped('Could not connect to Redis.');
        }

        $this->rateLimitStorage = new RedisRateLimitConfig()->createStorage($this->container);
    }

    #[PostCondition]
    protected function cleanup(): void
    {
        try {
            $this->redis->flush();
        } catch (Throwable) { // @mago-expect lint:no-empty-catch-clause
        }
    }

    #[Test]
    public function no_window_is_open_until_the_first_attempt(): void
    {
        $this->assertNull($this->rateLimitStorage->find('a'));
    }

    #[Test]
    public function attempts_accumulate_within_a_window(): void
    {
        $this->assertSame(1, $this->rateLimitStorage->increment('a', Duration::minute())->hits);
        $this->assertSame(2, $this->rateLimitStorage->increment('a', Duration::minute())->hits);
        $this->assertSame(5, $this->rateLimitStorage->increment('a', Duration::minute(), by: 3)->hits);

        $this->assertSame(5, $this->rateLimitStorage->find('a')->hits);
    }

    #[Test]
    public function counters_are_scoped_per_key(): void
    {
        $this->rateLimitStorage->increment('a', Duration::minute());
        $this->rateLimitStorage->increment('b', Duration::minute());
        $this->rateLimitStorage->increment('b', Duration::minute());

        $this->assertSame(1, $this->rateLimitStorage->find('a')->hits);
        $this->assertSame(2, $this->rateLimitStorage->find('b')->hits);
    }

    #[Test]
    public function the_window_is_opened_by_the_first_attempt_and_not_extended_by_later_ones(): void
    {
        $opened = $this->rateLimitStorage->increment('a', Duration::minutes(10));

        // A later attempt within the same window must not push the reset further away.
        $later = $this->rateLimitStorage->increment('a', Duration::minutes(10));

        $this->assertSame($opened->resetsAtInSeconds, $later->resetsAtInSeconds);
    }

    #[Test]
    public function the_window_expires_on_its_own(): void
    {
        $state = $this->rateLimitStorage->increment('a', Duration::seconds(1));

        $this->assertSame(1, $state->hits);

        // The counter carries a time to live, so it disappears without anyone removing it.
        sleep(2);

        $this->assertNull($this->rateLimitStorage->find('a'));
        $this->assertSame(1, $this->rateLimitStorage->increment('a', Duration::seconds(1))->hits);
    }

    #[Test]
    public function removing_a_key_discards_its_window(): void
    {
        $this->rateLimitStorage->increment('a', Duration::minute());
        $this->rateLimitStorage->increment('a', Duration::minute());

        $this->rateLimitStorage->remove('a');

        $this->assertNull($this->rateLimitStorage->find('a'));
        $this->assertSame(1, $this->rateLimitStorage->increment('a', Duration::minute())->hits);
    }

    #[Test]
    public function removing_a_key_that_was_never_incremented_is_harmless(): void
    {
        $this->rateLimitStorage->remove('a');

        $this->assertNull($this->rateLimitStorage->find('a'));
    }
}
