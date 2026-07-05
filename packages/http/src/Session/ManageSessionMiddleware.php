<?php

namespace Tempest\Http\Session;

use Tempest\Core\DeferredTasks;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Router\HttpMiddleware;
use Tempest\Router\HttpMiddlewareCallable;
use Tempest\Support\Priority;

/**
 * This middleware is responsible for creating the session and saving it on response.
 */
#[Priority(Priority::FRAMEWORK - 20)]
final readonly class ManageSessionMiddleware implements HttpMiddleware
{
    public function __construct(
        private SessionManager $sessionManager,
        private Session $session,
        private SessionConfig $config,
        private DeferredTasks $deferredTasks,
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
        if (random_int(min: 1, max: 50) === 1) {
            $this->scheduleSessionCleanup();
        }
    }
}
