<?php

declare(strict_types=1);

namespace Tempest\Idempotency\Middleware;

use Tempest\Cache\Cache;
use Tempest\DateTime\Duration;
use Tempest\Discovery\SkipDiscovery;
use Tempest\Http\GenericResponse;
use Tempest\Http\Request;
use Tempest\Http\Response;
use Tempest\Http\Status;
use Tempest\Idempotency\Attributes\Idempotent;
use Tempest\Idempotency\Attributes\IdempotentRoute;
use Tempest\Idempotency\Config\IdempotencyConfig;
use Tempest\Idempotency\Exceptions\IdempotencyMethodWasNotSupported;
use Tempest\Idempotency\Exceptions\IdempotencyPlatformWasNotSupported;
use Tempest\Idempotency\Fingerprint\HttpFingerprintGenerator;
use Tempest\Idempotency\IdempotencyScopeResolver;
use Tempest\Idempotency\Store\IdempotencyRecord;
use Tempest\Idempotency\Store\IdempotencyState;
use Tempest\Idempotency\Store\IdempotencyStore;
use Tempest\Idempotency\Store\StoredResponse;
use Tempest\Idempotency\Support\HeartbeatRenewer;
use Tempest\Idempotency\Support\IdempotencyKeyResolver;
use Tempest\Idempotency\Support\ProcessingOwner;
use Tempest\Idempotency\Support\ProcessingOwnerLiveness;
use Tempest\Idempotency\SupportedMethod;
use Tempest\Router\HttpMiddleware;
use Tempest\Router\HttpMiddlewareCallable;
use Tempest\Router\MatchedRoute;
use Throwable;

#[SkipDiscovery]
final readonly class IdempotencyMiddleware implements HttpMiddleware
{
    public function __construct(
        private Cache $cache,
        private IdempotencyStore $store,
        private IdempotencyKeyResolver $keyResolver,
        private HttpFingerprintGenerator $fingerprintGenerator,
        private IdempotencyConfig $config,
        private MatchedRoute $matchedRoute,
        private ProcessingOwner $processingOwner,
        private IdempotencyScopeResolver $scopeResolver,
    ) {}

    public function __invoke(Request $request, HttpMiddlewareCallable $next): Response
    {
        if (PHP_OS_FAMILY === 'Windows') {
            throw IdempotencyPlatformWasNotSupported::forWindows();
        }

        if (! SupportedMethod::isSupported($request->method)) {
            throw IdempotencyMethodWasNotSupported::forMethod($request->method);
        }

        $options = $this->resolveOptions();
        $key = trim($request->headers->get($options['header'], default: '') ?? '');

        if ($key === '') {
            if (! $options['requireKey']) {
                return $next($request);
            }

            return new GenericResponse(
                status: Status::BAD_REQUEST,
                body: ['error' => 'Missing idempotency key.'],
            );
        }

        $scope = $this->resolveScope($request);
        $fingerprint = $this->fingerprintGenerator->generate($request);
        $pendingTtlInSeconds = $options['pendingTtlInSeconds'];
        $pendingRecordTtlInSeconds = max($options['ttlInSeconds'], $pendingTtlInSeconds);
        $owner = $this->processingOwner->create();

        $lock = $this->cache->lock(
            key: $this->keyResolver->lockKey($scope, $key),
            duration: Duration::seconds($pendingTtlInSeconds),
            owner: $owner,
        );

        if (! $lock->acquire()) {
            return $this->inProgressResponse();
        }

        $pendingRecordCreated = false;

        try {
            if (($existingAfterLock = $this->store->find($scope, $key)) instanceof IdempotencyRecord) {
                if (! $this->shouldTakeOverPendingRecord($existingAfterLock, $fingerprint, $pendingTtlInSeconds)) {
                    return $this->resolveExistingRecord($existingAfterLock, $fingerprint);
                }

                $this->store->delete($scope, $key);
            }

            $this->store->savePending(
                scope: $scope,
                key: $key,
                fingerprint: $fingerprint,
                ttlInSeconds: $pendingRecordTtlInSeconds,
                pendingOwner: $owner,
                pendingHeartbeatAt: time(),
            );

            $pendingRecordCreated = true;

            $heartbeat = new HeartbeatRenewer();
            $heartbeat->start(
                store: $this->store,
                scope: $scope,
                key: $key,
                owner: $owner,
                intervalInSeconds: max(1, intdiv($pendingTtlInSeconds, 3)),
                recordTtlInSeconds: $pendingRecordTtlInSeconds,
            );

            try {
                $response = $next($request);
            } finally {
                $heartbeat->stop();
            }

            $this->store->saveCompleted(
                scope: $scope,
                key: $key,
                fingerprint: $fingerprint,
                response: StoredResponse::fromResponse($response),
                ttlInSeconds: $options['ttlInSeconds'],
            );

            return $response;
        } catch (Throwable $throwable) {
            if ($pendingRecordCreated) {
                $this->store->delete($scope, $key);
            }

            throw $throwable;
        } finally {
            $lock->release();
        }
    }

    private function resolveExistingRecord(IdempotencyRecord $record, string $fingerprint): Response
    {
        if ($record->fingerprint !== $fingerprint) {
            return new GenericResponse(
                status: Status::UNPROCESSABLE_CONTENT,
                body: ['error' => 'The idempotency key has already been used for another payload.'],
            );
        }

        if ($record->state === IdempotencyState::PENDING) {
            return $this->inProgressResponse();
        }

        $response = $record->response?->toResponse() ?? new GenericResponse(status: Status::NO_CONTENT);

        return $response->addHeader('idempotency-replayed', 'true');
    }

    private function shouldTakeOverPendingRecord(IdempotencyRecord $record, string $fingerprint, int $pendingTtlInSeconds): bool
    {
        if ($record->fingerprint !== $fingerprint) {
            return false;
        }

        if ($record->state !== IdempotencyState::PENDING) {
            return false;
        }

        return (
            $this->processingOwner->resolveLiveness(
                owner: $record->pendingOwner,
                heartbeatAt: $record->pendingHeartbeatAt,
                staleAfterInSeconds: $pendingTtlInSeconds,
            ) === ProcessingOwnerLiveness::DEAD
        );
    }

    private function inProgressResponse(): Response
    {
        return new GenericResponse(
            status: Status::CONFLICT,
            body: ['error' => 'Request is already being processed for this idempotency key.'],
        )->addHeader('retry-after', '1');
    }

    /** @return array{header: string, requireKey: bool, ttlInSeconds: int, pendingTtlInSeconds: int} */
    private function resolveOptions(): array
    {
        $idempotent = $this->resolveAttribute(Idempotent::class);
        $route = $this->resolveAttribute(IdempotentRoute::class);

        return [
            'header' => $route->header ?? $this->config->header,
            'requireKey' => $route->requireKey ?? $this->config->requireKey,
            'ttlInSeconds' => $idempotent->ttlInSeconds ?? $this->config->ttlInSeconds,
            'pendingTtlInSeconds' => $idempotent->pendingTtlInSeconds ?? $this->config->pendingTtlInSeconds,
        ];
    }

    /**
     * @template T of object
     * @param class-string<T> $attributeClass
     * @return T|null
     */
    private function resolveAttribute(string $attributeClass): ?object
    {
        $attribute = $this->matchedRoute
            ->route
            ->handler
            ->getAttribute($attributeClass);

        return $attribute ?? $this->matchedRoute
            ->route
            ->handler
            ->getDeclaringClass()
            ->getAttribute($attributeClass);
    }

    private function resolveScope(Request $request): string
    {
        $handler = $this->matchedRoute->route->handler;

        return sprintf(
            '%s::%s:%s:%s:%s',
            $handler->getDeclaringClass()->getName(),
            $handler->getName(),
            $request->method->value,
            $this->matchedRoute->route->uri,
            $this->scopeResolver->resolve($request),
        );
    }
}
