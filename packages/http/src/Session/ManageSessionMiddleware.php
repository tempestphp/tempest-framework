<?php

namespace Tempest\Http\Session;

use Tempest\Clock\Clock;
use Tempest\Core\DeferredTasks;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Router\HttpMiddleware;
use Tempest\Router\HttpMiddlewareCallable;
use Tempest\Router\SecFetchMode;
use Tempest\Support\Priority;

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
            if ($this->shouldPersist($request)) {
                $this->sessionManager->save($this->session);
            }

            match ($this->config->cleanupStrategy) {
                CleanupStrategy::EVERY_REQUEST => $this->scheduleSessionCleanup(),
                CleanupStrategy::RANDOM_REQUESTS => $this->maybeScheduleSessionCleanup(),
                default => null,
            };
        }
    }

    /**
     * Clients that don't send a session cookie, like API consumers, crawlers or load testing tools, get a new session on every request.
     * Those sessions are only persisted once they contain something worth keeping, so that they don't pile up in storage.
     */
    private function shouldPersist(Request $request): bool
    {
        if (! $this->session->isNew) {
            return true;
        }

        $data = $this->session->all();

        // The previous URL is tracked on every request, so it alone doesn't warrant persisting a new session.
        if (! $this->needsPreviousUrl($request)) {
            unset($data[PreviousUrl::PREVIOUS_URL_SESSION_KEY]);
        }

        return $data !== [];
    }

    /**
     * Browsers navigating between pages need the previous URL to be kept, because `Back` can't rely on the referer:
     * the request that redirects back may not send one, for example because of a `no-referrer` policy.
     * Other clients, like crawlers and API consumers, don't send `Sec-Fetch-*` headers, and can't submit forms
     * that redirect back either, since `PreventCrossSiteRequestsMiddleware` requires them.
     */
    private function needsPreviousUrl(Request $request): bool
    {
        return SecFetchMode::tryFrom($request->headers->get('sec-fetch-mode', default: '')) === SecFetchMode::NAVIGATE;
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

        if (! $this->claimSessionCleanup()) {
            return;
        }

        $this->scheduleSessionCleanup();
    }

    /**
     * Cleaning up has to go over all sessions, so it is throttled to keep its cost from growing along with the amount of sessions and traffic.
     * The last cleanup time is read, checked and updated while holding a lock, so that concurrent requests can't both claim the same cleanup.
     */
    private function claimSessionCleanup(): bool
    {
        $handle = @fopen(internal_storage_path('session-cleanup'), mode: 'c+');

        if ($handle === false) {
            return false;
        }

        try {
            // Another request holding the lock is already claiming the cleanup, so there's no need to wait for it.
            if (! flock($handle, LOCK_EX | LOCK_NB)) {
                return false;
            }

            $now = $this->clock->now()->getTimestamp()->getSeconds();
            $lastCleanupAt = (int) stream_get_contents($handle);

            if (($now - $lastCleanupAt) < self::RANDOM_REQUESTS_CLEANUP_INTERVAL) {
                return false;
            }

            ftruncate($handle, size: 0);
            rewind($handle);
            fwrite($handle, (string) $now);
            fflush($handle);

            return true;
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }
}
