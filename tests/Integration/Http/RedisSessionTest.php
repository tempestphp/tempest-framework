<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use PHPUnit\Framework\Attributes\PostCondition;
use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Clock\Clock;
use Tempest\DateTime\DateTimeInterface;
use Tempest\DateTime\Duration;
use Tempest\Http\Session\Config\RedisSessionConfig;
use Tempest\Http\Session\Managers\RedisSessionManager;
use Tempest\Http\Session\Session;
use Tempest\Http\Session\SessionCreated;
use Tempest\Http\Session\SessionDeleted;
use Tempest\Http\Session\SessionId;
use Tempest\Http\Session\SessionManager;
use Tempest\KeyValue\Redis\Redis;
use Tempest\Support\Random;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Throwable;

/**
 * @internal
 */
final class RedisSessionTest extends FrameworkIntegrationTestCase
{
    private SessionManager $manager {
        get => $this->container->get(SessionManager::class);
    }

    private Session $session {
        get => $this->container->get(Session::class);
    }

    #[PreCondition]
    protected function configure(): void
    {
        $this->container->config(new RedisSessionConfig(
            expiration: Duration::hours(2),
            prefix: 'test_session:',
        ));

        $this->container->singleton(SessionManager::class, fn () => new RedisSessionManager(
            clock: $this->container->get(Clock::class),
            redis: $this->container->get(Redis::class),
            config: $this->container->get(RedisSessionConfig::class),
        ));

        try {
            $this->container->get(Redis::class)->connect();
        } catch (Throwable) {
            $this->markTestSkipped('Could not connect to Redis.');
        }
    }

    #[PostCondition]
    protected function cleanup(): void
    {
        try {
            $this->container->get(Redis::class)->flush();
        } catch (Throwable) { // @mago-expect lint:no-empty-catch-clause
        }
    }

    #[Test]
    public function get_or_create_creates_new_session(): void
    {
        $this->eventBus->preventEventHandling();

        $sessionId = $this->createSessionId();
        $session = $this->manager->getOrCreate($sessionId);
        $this->manager->save($session);

        $this->assertInstanceOf(Session::class, $session);
        $this->assertEquals($sessionId, $session->id);

        $this->eventBus->assertDispatched(
            event: SessionCreated::class,
            callback: function (SessionCreated $event) use ($sessionId): void {
                $this->assertEquals($sessionId, $event->session->id);
            },
            count: 1,
        );
    }

    #[Test]
    public function get_or_create_loads_existing_session(): void
    {
        $sessionId = $this->createSessionId();
        $session = $this->manager->getOrCreate($sessionId);
        $session->set('key', 'value');

        $this->manager->save($session);

        $loaded = $this->manager->getOrCreate($sessionId);

        $this->assertEquals($sessionId, $loaded->id);
        $this->assertTrue($session->createdAt->isSameMinute($loaded->createdAt));
        $this->assertEquals('value', $loaded->get('key'));
    }

    #[Test]
    public function save_persists_session_to_redis(): void
    {
        $this->session->set('frieren', 'elf_mage');
        $this->manager->save($this->session);

        $this->assertSessionExistsInRedis($this->session->id);
        $this->assertSessionDataInRedis($this->session->id, ['frieren' => 'elf_mage']);
    }

    #[Test]
    public function save_persists_nested_data(): void
    {
        $data = [
            'members' => ['Frieren', 'Fern', 'Stark'],
            'location' => 'Northern Plateau',
            'quest' => [
                'name' => 'Journey to Ende',
                'progress' => 0.75,
            ],
        ];

        $this->session->set('party', $data);
        $this->manager->save($this->session);

        $this->assertSessionDataInRedis($this->session->id, ['party' => $data]);
    }

    #[Test]
    public function save_updates_last_active_timestamp(): void
    {
        $clock = $this->clock('2025-01-01 00:00:00');

        $this->manager->save($this->session);
        $originalTimestamp = $this->getSessionLastActiveTimestamp($this->session->id);

        $clock->plus(Duration::minutes(5));

        $this->session->set('action', 'spell_cast');
        $this->manager->save($this->session);
        $updatedTimestamp = $this->getSessionLastActiveTimestamp($this->session->id);

        $this->assertTrue($updatedTimestamp->after($originalTimestamp));
    }

    #[Test]
    public function delete_removes_session_from_redis(): void
    {
        $this->eventBus->preventEventHandling();

        $sessionId = $this->createSessionId();
        $session = $this->manager->getOrCreate($sessionId);
        $session->set('magic_type', 'offensive');

        $this->manager->save($session);

        $this->assertSessionExistsInRedis($sessionId);

        $this->manager->delete($session);

        $this->assertSessionNotExistsInRedis($sessionId);

        $this->eventBus->assertDispatched(
            event: SessionDeleted::class,
            callback: function (SessionDeleted $event) use ($sessionId): void {
                $this->assertEquals($sessionId, $event->id);
            },
            count: 1,
        );
    }

    #[Test]
    public function is_valid_checks_expiration(): void
    {
        $clock = $this->clock('2023-01-01 00:00:00');

        $this->container->config(new RedisSessionConfig(
            expiration: Duration::seconds(10),
            prefix: 'test_session:',
        ));

        $session = $this->manager->getOrCreate(new SessionId('expiration_test'));
        $this->manager->save($session);

        $this->assertTrue($this->manager->isValid($session));

        $clock->plus(Duration::seconds(5));
        $this->assertTrue($this->manager->isValid($session));

        $clock->plus(Duration::seconds(6));
        $this->assertFalse($this->manager->isValid($session));
    }

    #[Test]
    public function delete_expired_sessions_removes_old_records(): void
    {
        $this->eventBus->preventEventHandling();

        $clock = $this->clock('2023-01-01 00:00:00');

        $this->container->config(new RedisSessionConfig(
            expiration: Duration::minutes(30),
            prefix: 'test_session:',
        ));

        $activeSessionId = $this->createSessionId();
        $active = $this->manager->getOrCreate($activeSessionId);
        $active->set('status', 'active');

        $this->manager->save($active);

        $expiredSessionId = $this->createSessionId();
        $expired = $this->manager->getOrCreate($expiredSessionId);
        $expired->set('status', 'expired');

        $this->manager->save($expired);

        // expire the $expired one
        $clock->plus(Duration::minutes(35));

        // keep the first one active
        $this->manager->save($active);

        $this->assertSessionExistsInRedis($activeSessionId);
        $this->assertSessionExistsInRedis($expiredSessionId);

        $this->manager->deleteExpiredSessions();

        $this->assertSessionExistsInRedis($activeSessionId);
        $this->assertSessionNotExistsInRedis($expiredSessionId);

        $this->eventBus->assertDispatched(
            event: SessionDeleted::class,
            callback: function (SessionDeleted $event) use ($expiredSessionId): void {
                $this->assertEquals($expiredSessionId, $event->id);
            },
            count: 1,
        );
    }

    private function assertSessionExistsInRedis(SessionId $sessionId): void
    {
        $session = $this->getSessionFromRedis($sessionId);

        $this->assertNotNull($session, "Session {$sessionId} should exist in Redis");
    }

    private function assertSessionNotExistsInRedis(SessionId $sessionId): void
    {
        $session = $this->getSessionFromRedis($sessionId);

        $this->assertNull($session, "Session {$sessionId} should not exist in Redis");
    }

    private function assertSessionDataInRedis(SessionId $sessionId, array $data): void
    {
        $session = $this->getSessionFromRedis($sessionId);

        $this->assertNotNull($session, "Session {$sessionId} should exist in Redis");

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $session->data[$key], "Session data key '{$key}' should match expected value");
        }
    }

    private function getSessionLastActiveTimestamp(SessionId $sessionId): DateTimeInterface
    {
        $session = $this->getSessionFromRedis($sessionId);

        $this->assertNotNull($session, "Session {$sessionId} should exist in Redis");

        return $session->lastActiveAt;
    }

    private function getSessionFromRedis(SessionId $id): ?Session
    {
        $redis = $this->container->get(Redis::class);

        try {
            $content = $redis->get(sprintf('%s%s', 'test_session:', $id));
            return unserialize($content, ['allowed_classes' => true]);
        } catch (Throwable) {
            return null;
        }
    }

    private function createSessionId(): SessionId
    {
        return new SessionId(Random\uuid());
    }
}
