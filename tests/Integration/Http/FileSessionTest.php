<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use Tempest\Clock\Clock;
use Tempest\Core\FrameworkKernel;
use Tempest\DateTime\Duration;
use Tempest\Http\Session\Config\FileSessionConfig;
use Tempest\Http\Session\Managers\FileSessionManager;
use Tempest\Http\Session\Session;
use Tempest\Http\Session\SessionConfig;
use Tempest\Http\Session\SessionId;
use Tempest\Http\Session\SessionManager;
use Tempest\Support\Filesystem;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\Support\path;

/**
 * @internal
 */
final class FileSessionTest extends FrameworkIntegrationTestCase
{
    private string $path = __DIR__ . '/Fixtures/tmp';

    protected function setUp(): void
    {
        parent::setUp();

        Filesystem\ensure_directory_empty($this->path);

        $this->path = realpath($this->path);

        $this->container->get(FrameworkKernel::class)->internalStorage = realpath($this->path);
        $this->container->config(new FileSessionConfig(path: 'sessions', expiration: Duration::hours(2)));
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
        Filesystem\delete_directory($this->path);
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

    public function test_set_previous_url(): void
    {
        $session = $this->container->get(Session::class);
        $session->setPreviousUrl('http://localhost/previous');

        $this->assertEquals('http://localhost/previous', $session->getPreviousUrl());
    }

    public function test_is_valid(): void
    {
        $clock = $this->clock('2023-01-01 00:00:00');

        $this->container->config(new FileSessionConfig(
            path: 'test_sessions',
            expiration: Duration::second(),
        ));

        $sessionManager = $this->container->get(SessionManager::class);

        $this->assertFalse($sessionManager->isValid(new SessionId('unknown')));

        $session = $sessionManager->create(new SessionId('new'));

        $this->assertTrue($session->isValid());

        $clock->plus(1);

        $this->assertFalse($session->isValid());
    }
}
