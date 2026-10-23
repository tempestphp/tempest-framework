<?php

namespace Tempest\Http\Session;

use Tempest\Clock\Clock;
use Tempest\Core\DeferredTasks;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Router\HttpMiddleware;
use Tempest\Router\HttpMiddlewareCallable;
use Tempest\Support\Filesystem;
use Tempest\Support\Priority;
use Throwable;

use function Tempest\internal_storage_path;

/**
 * This middleware is responsible for creating the session and saving it on response.
 */
#[Priority(Priority::FRAMEWORK - 20)]
final readonly class ManageSessionMiddleware implements HttpMiddleware
{
    /**
     * Minimum amount of seconds between two cleanups when using the `RANDOM_REQUESTS` strategy.
     */
    private const int RANDOM_REQUESTS_CLEANUP_INTERVAL = 60;

    public function __construct(
        private SessionManager $sessionManager,
        private Session $session,
        private SessionConfig $config,
        private DeferredTasks $deferredTasks,
        private Clock $clock,
    ) {}

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        // Flash values are aged at the start of the request, before the response
        // body is rendered, so a value flashed on the previous request stays
        // readable this request and is expired on the next one.
        $this->session->cleanup();

        try {
            return $next($request);
        } finally {
            $this->sessionManager->save($this->session);

            match ($this->config->cleanupStrategy) {
                CleanupStrategy::EVERY_REQUEST => $this->scheduleSessionCleanup(),
                CleanupStrategy::RANDOM_REQUESTS => $this->maybeScheduleSessionCleanup(),
                default => null,
            };
        }
    }

    private function scheduleSessionCleanup(): void
    {
        $this->deferredTasks->add(
            task: $this->sessionManager->deleteExpiredSessions(...),
            name: 'tempest:session-cleanup',
        );
    }

    private function maybeScheduleSessionCleanup(): void
    {
        if (random_int(min: 1, max: 50) !== 1) {
            return;
        }

        // Cleaning up has to go over all sessions, so it is throttled to keep
        // its cost from growing along with the amount of sessions and traffic.
        $path = internal_storage_path('session-cleanup');
        $now = $this->clock->now()->getTimestamp()->getSeconds();

        try {
            $lastCleanupAt = Filesystem\is_file($path) ? (int) Filesystem\read_file($path) : 0;
        } catch (Throwable) {
            $lastCleanupAt = 0;
        }

        if (($now - $lastCleanupAt) < self::RANDOM_REQUESTS_CLEANUP_INTERVAL) {
            return;
        }

        // The cleanup is claimed before it runs, so that concurrent requests don't start one as well.
        Filesystem\write_file($path, (string) $now, flags: LOCK_EX);

        $this->scheduleSessionCleanup();
    }
}
