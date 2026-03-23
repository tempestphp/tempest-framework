<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Http;

use PHPUnit\Framework\Attributes\PostCondition;
use PHPUnit\Framework\Attributes\Test;
use Tempest\Clock\Clock;
use Tempest\Core\DeferredTasks;
use Tempest\DateTime\Duration;
use Tempest\Http\GenericRequest;
use Tempest\Http\GenericResponse;
use Tempest\Http\Method;
use Tempest\Http\Session\CleanupStrategy;
use Tempest\Http\Session\Config\FileSessionConfig;
use Tempest\Http\Session\ManageSessionMiddleware;
use Tempest\Http\Session\Session;
use Tempest\Http\Session\SessionId;
use Tempest\Http\Session\SessionManager;
use Tempest\Http\Status;
use Tempest\Router\HttpMiddlewareCallable;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

/**
 * @internal
 */
final class SessionCleanupStrategyTest extends FrameworkIntegrationTestCase
{
    #[PostCondition]
    protected function cleanup(): void
    {
        $this->clearDeferredTasks();
    }

    #[Test]
    public function every_request_schedules_cleanup_task(): void
    {
        $this->invokeMiddlewareWith(new FileSessionConfig(
            expiration: Duration::minutes(30),
            cleanupStrategy: CleanupStrategy::EVERY_REQUEST,
            path: 'tests/sessions',
        ));

        $this->assertArrayHasKey(
            'tempest:session-cleanup',
            $this->container->get(DeferredTasks::class)->getTasks(),
        );
    }

    #[Test]
    public function disabled_does_not_schedule_cleanup_task(): void
    {
        $this->invokeMiddlewareWith(new FileSessionConfig(
            expiration: Duration::minutes(30),
            cleanupStrategy: CleanupStrategy::DISABLED,
            path: 'tests/sessions',
        ));

        $this->assertArrayNotHasKey(
            'tempest:session-cleanup',
            $this->container->get(DeferredTasks::class)->getTasks(),
        );
    }

    private function invokeMiddlewareWith(FileSessionConfig $config): void
    {
        $this->clearDeferredTasks();

        $clock = $this->container->get(Clock::class);
        $now = $clock->now();

        $session = new Session(
            id: new SessionId('strategy-test'),
            createdAt: $now,
            lastActiveAt: $now,
        );

        $manager = new TestingSessionManager($clock);

        $middleware = new ManageSessionMiddleware(
            sessionManager: $manager,
            session: $session,
            config: $config,
            deferredTasks: $this->container->get(DeferredTasks::class),
        );

        $response = $middleware(
            request: new GenericRequest(method: Method::GET, uri: '/'),
            next: new HttpMiddlewareCallable(fn () => new GenericResponse(Status::OK)),
        );

        $this->assertSame(Status::OK, $response->status);
        $this->assertSame(1, $manager->saveCalls);
    }

    private function clearDeferredTasks(): void
    {
        $deferredTasks = $this->container->get(DeferredTasks::class);

        foreach (array_keys($deferredTasks->getTasks()) as $name) {
            $deferredTasks->forget($name);
        }
    }
}

final class TestingSessionManager implements SessionManager
{
    public int $saveCalls = 0;

    public function __construct(
        private readonly Clock $clock,
    ) {}

    public function getOrCreate(SessionId $id): Session
    {
        $now = $this->clock->now();

        return new Session(
            id: $id,
            createdAt: $now,
            lastActiveAt: $now,
        );
    }

    public function save(Session $session): void
    {
        $this->saveCalls++;
    }

    public function delete(Session $session): void {}

    public function isValid(Session $session): bool
    {
        return true;
    }

    public function deleteExpiredSessions(): void {}
}
