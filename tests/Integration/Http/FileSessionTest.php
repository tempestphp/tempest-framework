<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use Tempest\Clock\MockClock;
use Tempest\Http\Session\Managers\FileSessionManager;
use Tempest\Http\Session\Session;
use Tempest\Http\Session\SessionConfig;
use Tempest\Http\Session\SessionManager;
use function Tempest\path;
use Tempest\Testing\IntegrationTest;

/**
 * @internal
 * @small
 */
final class FileSessionTest extends IntegrationTest
{
    private string $path;

    protected function setUp(): void
    {
        parent::setUp();

        $this->path = __DIR__ . '/sessions';

        $this->container->config(new SessionConfig(path: $this->path));
        $this->container->singleton(
            SessionManager::class,
            fn () => new FileSessionManager(
                new MockClock(),
                $this->container->get(SessionConfig::class)
            )
        );
    }

    protected function tearDown(): void
    {
        array_map(unlink(...), glob("{$this->path}/*"));
        rmdir($this->path);
    }

    public function test_create_session_from_container()
    {
        $session = $this->container->get(Session::class);

        $this->assertInstanceOf(Session::class, $session);
    }

    public function test_put_get()
    {
        $session = $this->container->get(Session::class);

        $session->put('test', 'value');
        $value = $session->get('test');
        $this->assertEquals('value', $value);
    }

    public function test_remove()
    {
        $session = $this->container->get(Session::class);

        $session->put('test', 'value');
        $session->remove('test');
        $value = $session->get('test');
        $this->assertNull($value);
    }

    public function test_destroy()
    {
        $session = $this->container->get(Session::class);

        $path = path($this->path, (string) $session->id);
        $this->assertFileExists($path);
        $session->destroy();
        $this->assertFileDoesNotExist($path);
    }
}
