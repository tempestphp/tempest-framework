<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Clock\Clock;
use Tempest\Database\Database;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\DateTime\Duration;
use Tempest\EventBus\EventBus;
use Tempest\Http\Session\Config\DatabaseSessionConfig;
use Tempest\Http\Session\Installer\CreateSessionsTable;
use Tempest\Http\Session\Managers\DatabaseSession;
use Tempest\Http\Session\Managers\DatabaseSessionManager;
use Tempest\Http\Session\Session;
use Tempest\Http\Session\SessionConfig;
use Tempest\Http\Session\SessionDestroyed;
use Tempest\Http\Session\SessionId;
use Tempest\Http\Session\SessionManager;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

/**
 * @internal
 */
final class DatabaseSessionTest extends FrameworkIntegrationTestCase
{
    public Session $session {
        get => $this->container->get(Session::class);
    }

    #[PreCondition]
    protected function configure(): void
    {
        $this->container->config(new DatabaseSessionConfig(expiration: Duration::hours(2)));

        $this->container->singleton(SessionManager::class, fn () => new DatabaseSessionManager(
            $this->container->get(Clock::class),
            $this->container->get(SessionConfig::class),
            $this->container->get(Database::class),
        ));

        $this->database->reset(migrate: false);
        $this->database->migrate(CreateMigrationsTable::class, CreateSessionsTable::class);
    }

    #[Test]
    public function create_session_from_container(): void
    {
        $this->assertInstanceOf(Session::class, $this->session);
        $this->assertSessionExistsInDatabase($this->session->id);
    }

    #[Test]
    public function put_get(): void
    {
        $this->session->set('frieren', 'elf_mage');

        $this->assertEquals('elf_mage', $this->session->get('frieren'));
        $this->assertSessionDataInDatabase($this->session->id, ['frieren' => 'elf_mage']);

        $this->assertEquals('deceased_hero', $this->session->get('himmel', 'deceased_hero'));
    }

    #[Test]
    public function put_nested_data(): void
    {
        $data = [
            'members' => ['Frieren', 'Fern', 'Stark'],
            'location' => 'Northern Plateau',
            'quest' => [
                'name' => 'Journey to Ende',
                'progress' => 0.75,
                'completed_tasks' => ['Find Himmel statue', 'Pass mage exam'],
            ],
        ];

        $this->session->set('party', $data);

        $this->assertEquals($data, $this->session->get('party'));
        $this->assertSessionDataInDatabase($this->session->id, ['party' => $data]);
    }

    #[Test]
    public function remove(): void
    {
        $this->session->set('spell', 'Zoltraak');
        $this->session->set('caster', 'Frieren');

        $this->assertEquals('Zoltraak', $this->session->get('spell'));

        $this->session->remove('spell');

        $this->assertNull($this->session->get('spell'));
        $this->assertEquals('Frieren', $this->session->get('caster'));
        $this->assertSessionDataInDatabase($this->session->id, ['caster' => 'Frieren']);
    }

    #[Test]
    public function all(): void
    {
        $data = [
            'mage' => 'Frieren',
            'apprentice' => 'Fern',
            'warrior' => 'Stark',
        ];

        foreach ($data as $key => $value) {
            $this->session->set($key, $value);
        }

        $this->assertEquals($data, $this->session->all());
    }

    #[Test]
    public function destroy(): void
    {
        $manager = $this->container->get(SessionManager::class);
        $sessionId = new SessionId('test_session_destroy');

        $session = $manager->create($sessionId);
        $session->set('magic_type', 'offensive');

        $this->assertSessionExistsInDatabase($sessionId);

        $events = [];
        $eventBus = $this->container->get(EventBus::class);
        $eventBus->listen(function (SessionDestroyed $event) use (&$events): void {
            $events[] = $event;
        });

        $session->destroy();

        $this->assertSessionNotExistsInDatabase($sessionId);
        $this->assertCount(1, $events);
        $this->assertEquals((string) $sessionId, (string) $events[0]->id);
    }

    #[Test]
    public function set_previous_url(): void
    {
        $session = $this->container->get(Session::class);
        $session->setPreviousUrl('https://frieren.wiki/magic-academy');

        $this->assertEquals('https://frieren.wiki/magic-academy', $session->getPreviousUrl());
    }

    #[Test]
    public function is_valid(): void
    {
        $manager = $this->container->get(SessionManager::class);
        $sessionId = new SessionId('new_session_validity');

        $session = $manager->create($sessionId);

        $this->assertTrue($session->isValid());
        $this->assertTrue($manager->isValid($sessionId));
    }

    #[Test]
    public function is_valid_for_unknown_session(): void
    {
        $manager = $this->container->get(SessionManager::class);

        $this->assertFalse($manager->isValid(new SessionId('unknown_session')));
    }

    #[Test]
    public function is_valid_for_expired_session(): void
    {
        $clock = $this->clock('2023-01-01 00:00:00');

        $this->container->config(new DatabaseSessionConfig(expiration: Duration::second()));

        $manager = $this->container->get(SessionManager::class);
        $sessionId = new SessionId('expired_session');

        $session = $manager->create($sessionId);

        $this->assertTrue($session->isValid());

        $clock->plus(2);

        $this->assertFalse($session->isValid());
        $this->assertFalse($manager->isValid($sessionId));
    }

    #[Test]
    public function session_expires_based_on_last_activity(): void
    {
        $clock = $this->clock('2023-01-01 00:00:00');

        $this->container->config(new DatabaseSessionConfig(expiration: Duration::minutes(30)));

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
    public function session_reflash(): void
    {
        $session = $this->container->get(Session::class);

        $session->flash('success', 'Spell learned: Zoltraak');

        $this->assertEquals('Spell learned: Zoltraak', $session->get('success'));

        $session->reflash();
        $session->cleanup();

        $this->assertEquals('Spell learned: Zoltraak', $session->get('success'));
    }

    #[Test]
    public function cleanup_removes_expired_sessions(): void
    {
        $clock = $this->clock('2023-01-01 00:00:00');

        $this->container->config(new DatabaseSessionConfig(expiration: Duration::minutes(30)));

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
        $session = query(DatabaseSession::class)
            ->select()
            ->where('session_id', (string) $sessionId)
            ->first();

        $this->assertNotNull($session, "Session {$sessionId} should exist in database");
    }

    private function assertSessionNotExistsInDatabase(SessionId $sessionId): void
    {
        $session = query(DatabaseSession::class)
            ->select()
            ->where('session_id', (string) $sessionId)
            ->first();

        $this->assertNull($session, "Session {$sessionId} should not exist in database");
    }

    private function assertSessionDataInDatabase(SessionId $sessionId, array $data): void
    {
        $session = query(DatabaseSession::class)
            ->select()
            ->where('session_id', (string) $sessionId)
            ->first();

        $this->assertNotNull($session, "Session {$sessionId} should exist in database");

        $unserialized = unserialize($session->data);

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $unserialized[$key], "Session data key '{$key}' should match expected value");
        }
    }

    private function getSessionDataFromDatabase(SessionId $sessionId): array
    {
        $session = query(DatabaseSession::class)
            ->select()
            ->where('session_id', (string) $sessionId)
            ->first();

        return unserialize($session->data ?? '');
    }

    private function getSessionLastActiveTimestamp(SessionId $sessionId): \Tempest\DateTime\DateTime
    {
        $session = query(DatabaseSession::class)
            ->select()
            ->where('session_id', (string) $sessionId)
            ->first();

        $this->assertNotNull($session, "Session {$sessionId} should exist in database");

        return $session->last_active_at;
    }
}
