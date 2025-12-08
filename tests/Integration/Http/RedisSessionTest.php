<?php

declare(strict_types=1);

namespace Integration\Http;

use PHPUnit\Framework\Attributes\Test;
use Tempest\Clock\Clock;
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

/**
 * @internal
 */
final class RedisSessionTest extends FrameworkIntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->container->config(new RedisSessionConfig(expiration: Duration::hours(2)));
        $this->container->singleton(
            SessionManager::class,
            fn () => new RedisSessionManager(
                $this->container->get(Clock::class),
                $this->container->get(Redis::class),
                $this->container->get(SessionConfig::class),
            ),
        );
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
}
