<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Clock\Clock;
use Tempest\Database\Migrations\CreateMigrationsTable;
use Tempest\DateTime\Duration;
use Tempest\Http\Session\Config\DatabaseSessionConfig;
use Tempest\Http\Session\Installer\CreateSessionsTable;
use Tempest\Http\Session\Managers\DatabaseSession;
use Tempest\Http\Session\Managers\DatabaseSessionManager;
use Tempest\Http\Session\Session;
use Tempest\Http\Session\SessionConfig;
use Tempest\Http\Session\SessionCreated;
use Tempest\Http\Session\SessionDeleted;
use Tempest\Http\Session\SessionId;
use Tempest\Http\Session\SessionManager;
use Tempest\Support\Random;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Database\query;

/**
 * @internal
 */
final class DatabaseSessionTest extends FrameworkIntegrationTestCase
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
        $this->container->config(new DatabaseSessionConfig(expiration: Duration::hours(2)));

        $this->container->singleton(SessionManager::class, fn () => new DatabaseSessionManager(
            $this->container->get(Clock::class),
            $this->container->get(SessionConfig::class),
        ));

        $this->database->reset(migrate: false);
        $this->database->migrate(CreateMigrationsTable::class, CreateSessionsTable::class);
    }

    #[Test]
    public function get_or_create_creates_new_session(): void
    {
        $this->eventBus->preventEventHandling();

        $sessionId = $this->createSessionId();
        $session = $this->manager->getOrCreate($sessionId);

        $this->assertInstanceOf(Session::class, $session);
        $this->assertEquals($sessionId, $session->id);

        $this->manager->save($session);
        $this->assertSessionExistsInDatabase($sessionId);

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
    public function save_persists_session_to_database(): void
    {
        $this->session->set('frieren', 'elf_mage');
        $this->manager->save($this->session);

        $this->assertSessionExistsInDatabase($this->session->id);
        $this->assertSessionDataInDatabase($this->session->id, ['frieren' => 'elf_mage']);
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
                'completed_tasks' => ['Find Himmel statue', 'Pass mage exam'],
            ],
        ];

        $this->session->set('party', $data);
        $this->manager->save($this->session);

        $this->assertSessionDataInDatabase($this->session->id, ['party' => $data]);
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
    public function delete_removes_session_from_database(): void
    {
        $this->eventBus->preventEventHandling();

        $sessionId = $this->createSessionId();
        $session = $this->manager->getOrCreate($sessionId);
        $session->set('magic_type', 'offensive');

        $this->manager->save($session);

        $this->assertSessionExistsInDatabase($sessionId);

        $this->manager->delete($session);

        $this->assertSessionNotExistsInDatabase($sessionId);

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
        $sessionId = $this->createSessionId();
        $session = $this->manager->getOrCreate($sessionId);
        $this->manager->save($session);

        $this->assertTrue($this->manager->isValid($session));
    }

    #[Test]
    public function is_valid_for_expired_session(): void
    {
        $clock = $this->clock('2023-01-01 00:00:00');

        $this->container->config(new DatabaseSessionConfig(expiration: Duration::second()));

        $sessionId = $this->createSessionId();
        $session = $this->manager->getOrCreate($sessionId);
        $this->manager->save($session);

        $this->assertTrue($this->manager->isValid($session));

        $clock->plus(2);

        $this->assertFalse($this->manager->isValid($session));
    }

    #[Test]
    public function delete_expired_sessions_removes_old_records(): void
    {
        $this->eventBus->preventEventHandling();

        $clock = $this->clock('2025-01-01 00:00:00');

        $this->container->config(new DatabaseSessionConfig(expiration: Duration::minutes(30)));

        $activeId = $this->createSessionId();
        $active = $this->manager->getOrCreate($activeId);
        $active->set('status', 'active');

        $this->manager->save($active);

        $expiredId = $this->createSessionId();
        $expired = $this->manager->getOrCreate($expiredId);
        $expired->set('status', 'expired');

        $this->manager->save($expired);

        // expire the second session
        $clock->plus(Duration::minutes(35));

        // keep active session fresh
        $this->manager->save($active);

        $this->assertSessionExistsInDatabase($activeId);
        $this->assertSessionExistsInDatabase($expiredId);

        $this->manager->deleteExpiredSessions();

        $this->assertSessionExistsInDatabase($activeId);
        $this->assertSessionNotExistsInDatabase($expiredId);

        $this->eventBus->assertDispatched(
            event: SessionDeleted::class,
            callback: function (SessionDeleted $event) use ($expiredId): void {
                $this->assertEquals($expiredId, $event->id);
            },
            count: 1,
        );
    }

    private function assertSessionExistsInDatabase(SessionId $sessionId): void
    {
        $session = query(DatabaseSession::class)
            ->select()
            ->where('id', (string) $sessionId)
            ->first();

        $this->assertNotNull($session, "Session {$sessionId} should exist in database");
    }

    private function assertSessionNotExistsInDatabase(SessionId $sessionId): void
    {
        $session = query(DatabaseSession::class)
            ->select()
            ->where('id', (string) $sessionId)
            ->first();

        $this->assertNull($session, "Session {$sessionId} should not exist in database");
    }

    private function assertSessionDataInDatabase(SessionId $sessionId, array $data): void
    {
        $session = query(DatabaseSession::class)
            ->select()
            ->where('id', (string) $sessionId)
            ->first();

        $this->assertNotNull($session, "Session {$sessionId} should exist in database");

        $unserialized = unserialize($session->data);

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $unserialized[$key], "Session data key '{$key}' should match expected value");
        }
    }

    private function getSessionLastActiveTimestamp(SessionId $sessionId): \Tempest\DateTime\DateTime
    {
        $session = query(DatabaseSession::class)
            ->select()
            ->where('id', (string) $sessionId)
            ->first();

        $this->assertNotNull($session, "Session {$sessionId} should exist in database");

        return $session->last_active_at;
    }

    private function createSessionId(): SessionId
    {
        return new SessionId(Random\uuid());
    }
}
