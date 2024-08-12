<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use Tempest\Clock\Clock;
use Tempest\Http\Session\Managers\FileSessionManager;
use Tempest\Http\Session\Session;
use Tempest\Http\Session\SessionConfig;
use Tempest\Http\Session\SessionId;
use Tempest\Http\Session\SessionManager;
use function Tempest\path;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 * @small
 */
final class FileSessionTest extends FrameworkIntegrationTestCase
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
                $this->container->get(Clock::class),
                $this->container->get(SessionConfig::class)
            )
        );
    }

    protected function tearDown(): void
    {
        array_map(unlink(...), glob("{$this->path}/*"));
        rmdir($this->path);
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

        $path = path($this->path, (string) $session->id);
        $this->assertFileExists($path);
        $session->destroy();
        $this->assertFileDoesNotExist($path);
    }

    public function test_is_valid(): void
    {
        $clock = $this->clock('2023-01-01 00:00:00');

        $this->container->config(new SessionConfig(
            path: __DIR__ . '/sessions',
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
