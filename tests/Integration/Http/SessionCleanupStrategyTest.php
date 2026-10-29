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
use Tempest\Support\Filesystem;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;

use function Tempest\internal_storage_path;

/**
 * @internal
 */
final class SessionCleanupStrategyTest extends FrameworkIntegrationTestCase
{
    #[PostCondition]
    protected function cleanup(): void
    {
        $this->clearDeferredTasks();

        Filesystem\delete(internal_storage_path('session-cleanup'));
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

    #[Test]
    public function random_requests_schedules_cleanup_at_most_once_per_interval(): void
    {
        $clock = $this->clock('2025-01-01 00:00:00');

        $config = new FileSessionConfig(
            expiration: Duration::minutes(30),
            cleanupStrategy: CleanupStrategy::RANDOM_REQUESTS,
            path: 'tests/sessions',
        );

        // The odds of a cleanup are 1 in 50 per request, so 1000 requests are virtually guaranteed to trigger one.
        $this->assertTrue($this->cleanupScheduledWithin(1000, $config));
        $this->assertFalse($this->cleanupScheduledWithin(1000, $config));

        $clock->plus(Duration::seconds(30));
        $this->assertFalse($this->cleanupScheduledWithin(1000, $config));

        $clock->plus(Duration::seconds(31));
        $this->assertTrue($this->cleanupScheduledWithin(1000, $config));
    }

    #[Test]
    public function random_requests_does_not_schedule_cleanup_while_another_request_claims_it(): void
    {
        $this->clock('2025-01-01 00:00:00');

        $config = new FileSessionConfig(
            expiration: Duration::minutes(30),
            cleanupStrategy: CleanupStrategy::RANDOM_REQUESTS,
            path: 'tests/sessions',
        );

        // Simulates a concurrent request that is in the middle of claiming the cleanup
        $handle = fopen(internal_storage_path('session-cleanup'), mode: 'c+');
        flock($handle, LOCK_EX);

        try {
            $this->assertFalse($this->cleanupScheduledWithin(1000, $config));
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }

        $this->assertTrue($this->cleanupScheduledWithin(1000, $config));
    }

    private function cleanupScheduledWithin(int $requests, FileSessionConfig $config): bool
    {
        for ($i = 0; $i < $requests; $i++) {
            $this->invokeMiddlewareWith($config);

            if (array_key_exists('tempest:session-cleanup', $this->container->get(DeferredTasks::class)->getTasks())) {
                return true;
            }
        }

        return false;
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
            clock: $clock,
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
