<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use Closure;
use PHPUnit\Framework\Attributes\PostCondition;
use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Clock\Clock;
use Tempest\Core\DeferredTasks;
use Tempest\Core\FrameworkKernel;
use Tempest\DateTime\Duration;
use Tempest\Http\GenericRequest;
use Tempest\Http\GenericResponse;
use Tempest\Http\Method;
use Tempest\Http\Session\CleanupStrategy;
use Tempest\Http\Session\Config\FileSessionConfig;
use Tempest\Http\Session\Managers\FileSessionManager;
use Tempest\Http\Session\ManageSessionMiddleware;
use Tempest\Http\Session\PreviousUrl;
use Tempest\Http\Session\Session;
use Tempest\Http\Session\SessionId;
use Tempest\Http\Status;
use Tempest\Router\HttpMiddlewareCallable;
use Tempest\Support\Filesystem;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class ManageSessionMiddlewareTest extends FrameworkIntegrationTestCase
{
    private string $path = __DIR__ . '/Fixtures/tmp';

    private FileSessionConfig $config;

    private FileSessionManager $manager;

    #[PreCondition]
    protected function configure(): void
    {
        Filesystem\ensure_directory_empty($this->path);

        $this->path = realpath($this->path);
        $this->container->get(FrameworkKernel::class)->internalStorage = $this->path;

        $this->config = new FileSessionConfig(
            expiration: Duration::hours(2),
            cleanupStrategy: CleanupStrategy::DISABLED,
            path: 'sessions',
        );

        $this->manager = new FileSessionManager(
            clock: $this->container->get(Clock::class),
            sessionConfig: $this->config,
        );
    }

    #[PostCondition]
    protected function cleanup(): void
    {
        Filesystem\delete_directory($this->path);
    }

    #[Test]
    public function new_session_without_data_is_not_persisted(): void
    {
        $session = $this->manager->getOrCreate(new SessionId('new-session'));

        $this->handle($session);

        $this->assertFileDoesNotExist($this->sessionPath($session));
    }

    #[Test]
    public function new_session_with_only_previous_url_is_not_persisted_for_non_browser_clients(): void
    {
        $session = $this->manager->getOrCreate(new SessionId('new-session'));

        $this->handle($session, fn (Session $session) => $session->set(PreviousUrl::PREVIOUS_URL_SESSION_KEY, '/dashboard'));

        $this->assertFileDoesNotExist($this->sessionPath($session));
    }

    #[Test]
    public function new_session_with_only_previous_url_is_not_persisted_for_browser_navigation_with_referer(): void
    {
        $session = $this->manager->getOrCreate(new SessionId('new-session'));

        $this->handle(
            session: $session,
            controller: fn (Session $session) => $session->set(PreviousUrl::PREVIOUS_URL_SESSION_KEY, '/dashboard'),
            headers: ['sec-fetch-mode' => 'navigate', 'referer' => 'https://tempestphp.com/'],
        );

        $this->assertFileDoesNotExist($this->sessionPath($session));
    }

    #[Test]
    public function new_session_with_only_previous_url_is_persisted_for_browser_navigation_without_referer(): void
    {
        $session = $this->manager->getOrCreate(new SessionId('new-session'));

        $this->handle(
            session: $session,
            controller: fn (Session $session) => $session->set(PreviousUrl::PREVIOUS_URL_SESSION_KEY, '/dashboard'),
            headers: ['sec-fetch-mode' => 'navigate'],
        );

        $this->assertFileExists($this->sessionPath($session));
    }

    #[Test]
    public function new_session_with_data_is_persisted(): void
    {
        $session = $this->manager->getOrCreate(new SessionId('new-session'));

        $this->handle($session, fn (Session $session) => $session->set('user', 'brent'));

        $this->assertFileExists($this->sessionPath($session));
        $this->assertFalse($session->isNew);
    }

    #[Test]
    public function new_session_with_flash_value_is_persisted(): void
    {
        $session = $this->manager->getOrCreate(new SessionId('new-session'));

        $this->handle($session, fn (Session $session) => $session->flash('success', 'Saved!'));

        $this->assertFileExists($this->sessionPath($session));
    }

    #[Test]
    public function existing_session_is_persisted_after_being_cleared(): void
    {
        $session = $this->manager->getOrCreate(new SessionId('existing-session'));

        $this->handle($session, fn (Session $session) => $session->set('user', 'brent'));
        $this->handle($session, fn (Session $session) => $session->clear());

        $this->assertSame([], $this->manager->getOrCreate(new SessionId('existing-session'))->all());
    }

    #[Test]
    public function loaded_session_is_not_new(): void
    {
        $session = $this->manager->getOrCreate(new SessionId('existing-session'));
        $this->assertTrue($session->isNew);

        $this->handle($session, fn (Session $session) => $session->set('user', 'brent'));

        $this->assertFalse($this->manager->getOrCreate(new SessionId('existing-session'))->isNew);
    }

    private function handle(Session $session, ?Closure $controller = null, array $headers = []): void
    {
        $middleware = new ManageSessionMiddleware(
            sessionManager: $this->manager,
            session: $session,
            config: $this->config,
            deferredTasks: $this->container->get(DeferredTasks::class),
            clock: $this->container->get(Clock::class),
        );

        $middleware(
            request: new GenericRequest(method: Method::GET, uri: '/', headers: $headers),
            next: new HttpMiddlewareCallable(function () use ($session, $controller) {
                if ($controller !== null) {
                    $controller($session);
                }

                return new GenericResponse(Status::OK);
            }),
        );
    }

    private function sessionPath(Session $session): string
    {
        return $this->path . '/sessions/' . $session->id;
    }
}
