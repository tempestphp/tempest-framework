<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use Tempest\Clock\Clock;
use Tempest\Core\FrameworkKernel;
use Tempest\Core\Kernel;
use Tempest\Filesystem\LocalFilesystem;
use Tempest\Router\Session\Managers\FileSessionManager;
use Tempest\Router\Session\Session;
use Tempest\Router\Session\SessionConfig;
use Tempest\Router\Session\SessionId;
use Tempest\Router\Session\SessionManager;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use function Tempest\internal_storage_path;
use function Tempest\Support\path;

/**
 * @internal
 */
final class FileSessionTest extends FrameworkIntegrationTestCase
{
    private string $path = __DIR__ . '/Fixtures/tmp';

    private LocalFilesystem $filesystem;

    protected function setUp(): void
    {
        parent::setUp();

        $this->filesystem = new LocalFilesystem();
        $this->filesystem->deleteDirectory($this->path, recursive: true);
        $this->filesystem->ensureDirectoryExists($this->path);

        $this->path = realpath($this->path);

        $this->container->get(FrameworkKernel::class)->internalStorage = realpath($this->path);

        $this->container->config(new SessionConfig(path: 'sessions'));
        $this->container->singleton(
            SessionManager::class,
            fn () => new FileSessionManager(
                $this->container->get(Clock::class),
                $this->container->get(SessionConfig::class),
            ),
        );
    }

    protected function tearDown(): void
    {
        $this->filesystem->deleteDirectory($this->path, recursive: true);
    }

    public function test_create_session_from_container(): void
    {
        $session = $this->container->get(Session::class);

        $this->assertInstanceOf(Session::class, $session);
    }

    public function test_put_get(): void
    {
        $session = $this->container->get(Session::class);

        $session->set('test', 'value');

        $value = $session->get('test');
        $this->assertEquals('value', $value);
    }

    public function test_remove(): void
    {
        $session = $this->container->get(Session::class);

        $session->set('test', 'value');
        $session->remove('test');

        $value = $session->get('test');
        $this->assertNull($value);
    }

    public function test_destroy(): void
    {
        $session = $this->container->get(Session::class);
        $path = path($this->path, 'sessions', (string) $session->id)->toString();

        $this->assertFileExists($path);

        $session->destroy();

        $this->assertFileDoesNotExist($path);
    }

    public function test_is_valid(): void
    {
        $clock = $this->clock('2023-01-01 00:00:00');

        $this->container->config(new SessionConfig(
            path: 'test_sessions',
            expirationInSeconds: 1,
        ));

        $sessionManager = $this->container->get(SessionManager::class);

        $this->assertFalse($sessionManager->isValid(new SessionId('unknown')));

        $session = $sessionManager->create(new SessionId('new'));

        $this->assertTrue($session->isValid());

        $clock->changeTime(seconds: 1);

        $this->assertFalse($session->isValid());
    }
}
