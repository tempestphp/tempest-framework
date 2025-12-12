<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Clock\Clock;
use Tempest\DateTime\DateTimeInterface;
use Tempest\DateTime\Duration;
use Tempest\EventBus\EventBus;
use Tempest\Http\Session\Config\RedisSessionConfig;
use Tempest\Http\Session\Managers\RedisSessionManager;
use Tempest\Http\Session\Session;
use Tempest\Http\Session\SessionConfig;
use Tempest\Http\Session\SessionDestroyed;
use Tempest\Http\Session\SessionId;
use Tempest\Http\Session\SessionManager;
use Tempest\KeyValue\Redis\Redis;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Throwable;

/**
 * @internal
 */
final class RedisSessionTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->container->config(new RedisSessionConfig(expiration: Duration::hours(2), prefix: 'test_session:'));
        $this->container->singleton(
            SessionManager::class,
            fn () => new RedisSessionManager(
                $this->container->get(Clock::class),
                $this->container->get(Redis::class),
                $this->container->get(SessionConfig::class),
            ),
        );
    }

    protected function tearDown(): void
    {
        $this->container->get(Redis::class)->flush();
    }

    #[Test]
    public function create_session_from_container(): void
    {
        $session = $this->container->get(Session::class);

        $this->assertInstanceOf(Session::class, $session);
    }

    #[Test]
    public function put_get(): void
    {
        $session = $this->container->get(Session::class);

        $session->set('test', 'value');

        $value = $session->get('test');
        $this->assertEquals('value', $value);
    }

    #[Test]
    public function remove(): void
    {
        $session = $this->container->get(Session::class);

        $session->set('test', 'value');
        $session->remove('test');

        $value = $session->get('test');
        $this->assertNull($value);
    }

    #[Test]
    public function destroy(): void
    {
        $manager = $this->container->get(SessionManager::class);
        $sessionId = new SessionId('test_session_destroy');

        $session = $manager->create($sessionId);
        $session->set('magic_type', 'offensive');

        $this->assertTrue($manager->isValid($sessionId));

        $events = [];
        $eventBus = $this->container->get(EventBus::class);
        $eventBus->listen(function (SessionDestroyed $event) use (&$events): void {
            $events[] = $event;
        });

        $session->destroy();

        $this->assertFalse($manager->isValid($sessionId));
        $this->assertCount(1, $events);
        $this->assertEquals((string) $sessionId, (string) $events[0]->id);
    }

    #[Test]
    public function set_previous_url(): void
    {
        $session = $this->container->get(Session::class);
        $session->setPreviousUrl('http://localhost/previous');

        $this->assertEquals('http://localhost/previous', $session->getPreviousUrl());
    }

    #[Test]
    public function is_valid(): void
    {
        $clock = $this->clock('2023-01-01 00:00:00');

        $this->container->config(new RedisSessionConfig(
            expiration: Duration::second(),
            prefix: 'test_session:',
        ));

        $sessionManager = $this->container->get(SessionManager::class);

        $this->assertFalse($sessionManager->isValid(new SessionId('unknown')));

        $session = $sessionManager->create(new SessionId('new'));

        $this->assertTrue($session->isValid());

        $clock->plus(1);

        $this->assertFalse($session->isValid());
    }

    #[Test]
    public function session_reflash(): void
    {
        $session = $this->container->get(Session::class);

        $session->flash('test', 'value');
        $session->flash('test2', ['key' => 'value']);

        $this->assertEquals('value', $session->get('test'));

        $session->reflash();
        $session->cleanup();

        $this->assertEquals('value', $session->get('test'));
        $this->assertEquals(['key' => 'value'], $session->get('test2'));
    }

    #[Test]
    public function session_expires_based_on_last_activity(): void
    {
        $clock = $this->clock('2023-01-01 00:00:00');

        $this->container->config(new RedisSessionConfig(
            expiration: Duration::minutes(30),
            prefix: 'test_session:',
        ));

        $manager = $this->container->get(SessionManager::class);
        $sessionId = new SessionId('last_activity_test');

        // Create session
        $session = $manager->create($sessionId);
        $this->assertTrue($session->isValid());

        $clock->plus(Duration::minutes(25));
        $this->assertTrue($session->isValid());

        // Perform activity
        $session->set('activity', 'user_action');
        $clock->plus(Duration::minutes(25));
        $this->assertTrue($session->isValid());
        $this->assertTrue($manager->isValid($sessionId));

        // Move forward another 10 minutes, now 35 minutes from last activity
        $clock->plus(Duration::minutes(10));
        $this->assertFalse($session->isValid());
        $this->assertFalse($manager->isValid($sessionId));
    }

    #[Test]
    public function cleanup_removes_expired_sessions(): void
    {
        $clock = $this->clock('2023-01-01 00:00:00');

        $this->container->config(new RedisSessionConfig(expiration: Duration::minutes(30), prefix: 'test_session:'));

        $manager = $this->container->get(SessionManager::class);

        $activeSessionId = new SessionId('active_session');
        $activeSession = $manager->create($activeSessionId);
        $activeSession->set('status', 'active');

        $clock->minus(Duration::hour());
        $expiredSessionId = new SessionId('expired_session');
        $expiredSession = $manager->create($expiredSessionId);
        $expiredSession->set('status', 'expired');

        $clock->plus(Duration::hour());

        $this->assertSessionExistsInDatabase($activeSessionId);
        $this->assertSessionExistsInDatabase($expiredSessionId);

        $manager->cleanup();

        $this->assertSessionExistsInDatabase($activeSessionId);
        $this->assertSessionNotExistsInDatabase($expiredSessionId);
    }

    #[Test]
    public function session_updates_last_active_timestamp(): void
    {
        $clock = $this->clock('2023-01-01 12:00:00');

        $manager = $this->container->get(SessionManager::class);
        $sessionId = new SessionId('timestamp_test');

        $session = $manager->create($sessionId);
        $originalTimestamp = $this->getSessionLastActiveTimestamp($sessionId);

        $clock->plus(Duration::minutes(5));

        $session->set('action', 'spell_cast');
        $updatedTimestamp = $this->getSessionLastActiveTimestamp($sessionId);

        $this->assertTrue($updatedTimestamp->after($originalTimestamp));
    }

    #[Test]
    public function session_persists_csrf_token(): void
    {
        $session = $this->container->get(Session::class);
        $token = $session->token;

        $data = $this->getSessionDataFromDatabase($session->id);

        $this->assertEquals($token, $data[Session::CSRF_TOKEN_KEY]);
        $this->assertEquals($token, $session->token);
    }

    private function assertSessionExistsInDatabase(SessionId $sessionId): void
    {
        $session = $this->getSessionFromDatabase($sessionId);

        $this->assertNotNull($session, "Session {$sessionId} should exist in database");
    }

    private function assertSessionNotExistsInDatabase(SessionId $sessionId): void
    {
        $session = $this->getSessionFromDatabase($sessionId);

        $this->assertNull($session, "Session {$sessionId} should not exist in database");
    }

    private function getSessionLastActiveTimestamp(SessionId $sessionId): DateTimeInterface
    {
        $session = $this->getSessionFromDatabase($sessionId);

        $this->assertNotNull($session, "Session {$sessionId} should exist in database");

        return $session->lastActiveAt;
    }

    private function getSessionFromDatabase(SessionId $id): ?Session
    {
        $redis = $this->container->get(Redis::class);

        try {
            $content = $redis->get(sprintf('%s%s', 'test_session:', $id));
            return unserialize($content, ['allowed_classes' => true]);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @return array<mixed>
     */
    private function getSessionDataFromDatabase(SessionId $id): array
    {
        return $this->getSessionFromDatabase($id)->data ?? [];
    }
}
