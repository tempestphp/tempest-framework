<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use PHPUnit\Framework\Attributes\PostCondition;
use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Clock\Clock;
use Tempest\Core\FrameworkKernel;
use Tempest\DateTime\Duration;
use Tempest\Http\Session\Config\FileSessionConfig;
use Tempest\Http\Session\Managers\FileSessionManager;
use Tempest\Http\Session\Session;
use Tempest\Http\Session\SessionCreated;
use Tempest\Http\Session\SessionDeleted;
use Tempest\Http\Session\SessionId;
use Tempest\Http\Session\SessionManager;
use Tempest\Support\Filesystem;
use Tempest\Support\Path;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class FileSessionTest extends FrameworkIntegrationTestCase
{
    private string $path = __DIR__ . '/Fixtures/tmp';

    private SessionManager $manager {
        get => $this->container->get(SessionManager::class);
    }

    private Session $session {
        get => $this->container->get(Session::class);
    }

    #[PreCondition]
    protected function configure(): void
    {
        Filesystem\ensure_directory_empty($this->path);

        $this->path = realpath($this->path);
        $this->container->get(FrameworkKernel::class)->internalStorage = realpath($this->path);

        $this->container->config(new FileSessionConfig(
            path: 'sessions',
            expiration: Duration::hours(2),
        ));

        $this->container->singleton(SessionManager::class, fn () => new FileSessionManager(
            $this->container->get(Clock::class),
            $this->container->get(FileSessionConfig::class),
        ));
    }

    #[PostCondition]
    protected function cleanup(): void
    {
        Filesystem\delete_directory($this->path);
    }

    #[Test]
    public function get_or_create_creates_new_session(): void
    {
        $this->eventBus->preventEventHandling();

        $sessionId = new SessionId('new_session');
        $session = $this->manager->getOrCreate($sessionId);

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
        $sessionId = new SessionId('existing_session');
        $session = $this->manager->getOrCreate($sessionId);
        $session->set('key', 'value');

        $this->manager->save($session);

        $loaded = $this->manager->getOrCreate($sessionId);

        $this->assertEquals($sessionId, $loaded->id);
        $this->assertTrue($session->createdAt->isSameMinute($loaded->createdAt));
        $this->assertEquals('value', $loaded->get('key'));
    }

    #[Test]
    public function save_persists_session_to_file(): void
    {
        $this->session->set('test_key', 'test_value');
        $this->manager->save($this->session);

        $path = Path\normalize($this->path, 'sessions', (string) $this->session->id);

        $this->assertFileExists($path);

        $content = unserialize(file_get_contents($path));

        $this->assertInstanceOf(Session::class, $content);
        $this->assertEquals('test_value', $content->get('test_key'));
    }

    #[Test]
    public function save_updates_last_active_timestamp(): void
    {
        $clock = $this->clock('2025-01-01 00:00:00');
        $original = $this->session->lastActiveAt;

        // session created with current timestamp
        $this->assertTrue($this->session->lastActiveAt->equals($clock->now()));

        // save it 5 minutes later
        $clock->plus(Duration::minutes(5));
        $this->manager->save($this->session);

        // last active at has updated
        $this->assertTrue($this->session->lastActiveAt->after($original));
    }

    #[Test]
    public function delete_removes_session_file(): void
    {
        $this->eventBus->preventEventHandling();

        $this->manager->save($this->session);

        $path = Path\normalize($this->path, 'sessions', (string) $this->session->id);

        $this->assertFileExists($path);

        $this->manager->delete($this->session);

        $this->assertFileDoesNotExist($path);

        $this->eventBus->assertDispatched(
            event: SessionDeleted::class,
            callback: function (SessionDeleted $event): void {
                $this->assertEquals($this->session->id, $event->id);
            },
            count: 1,
        );
    }

    #[Test]
    public function is_valid_checks_expiration(): void
    {
        $clock = $this->clock('2025-01-01 00:00:00');

        $this->container->config(new FileSessionConfig(
            path: 'test_sessions',
            expiration: Duration::seconds(10),
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
    public function delete_expired_sessions_removes_old_files(): void
    {
        $this->eventBus->preventEventHandling();

        $clock = $this->clock('2023-01-01 00:00:00');

        $this->container->config(new FileSessionConfig(
            path: 'test_sessions',
            expiration: Duration::minutes(30),
        ));

        $active = $this->manager->getOrCreate(new SessionId('active'));
        $this->manager->save($active);

        $expired = $this->manager->getOrCreate(new SessionId('expired'));
        $this->manager->save($expired);

        // we expire the $expired one
        $clock->plus(Duration::minutes(35));

        // keep active session fresh
        $this->manager->save($active);

        $activePath = Path\normalize($this->path, 'test_sessions', (string) $active->id);
        $expiredPath = Path\normalize($this->path, 'test_sessions', (string) $expired->id);

        $this->assertFileExists($activePath);
        $this->assertFileExists($expiredPath);

        $this->manager->deleteExpiredSessions();

        $this->assertFileExists($activePath);
        $this->assertFileDoesNotExist($expiredPath);

        $this->eventBus->assertDispatched(
            event: SessionDeleted::class,
            callback: function (SessionDeleted $event) use ($expired): void {
                $this->assertEquals($expired->id, $event->id);
            },
            count: 1,
        );
    }
}
