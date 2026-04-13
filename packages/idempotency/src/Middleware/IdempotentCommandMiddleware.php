<?php

declare(strict_types=1);

namespace Tempest\Idempotency\Middleware;

use Tempest\Cache\Cache;
use Tempest\CommandBus\CommandBusConfig;
use Tempest\CommandBus\CommandBusMiddleware;
use Tempest\CommandBus\CommandBusMiddlewareCallable;
use Tempest\DateTime\Duration;
use Tempest\Idempotency\Attributes\Idempotent;
use Tempest\Idempotency\Config\IdempotencyConfig;
use Tempest\Idempotency\Exceptions\IdempotencyKeyWasAlreadyUsed;
use Tempest\Idempotency\Exceptions\IdempotencyPlatformWasNotSupported;
use Tempest\Idempotency\Fingerprint\CommandFingerprintGenerator;
use Tempest\Idempotency\HasIdempotencyKey;
use Tempest\Idempotency\Store\IdempotencyRecord;
use Tempest\Idempotency\Store\IdempotencyState;
use Tempest\Idempotency\Store\IdempotencyStore;
use Tempest\Idempotency\Support\HeartbeatRenewer;
use Tempest\Idempotency\Support\IdempotencyKeyResolver;
use Tempest\Idempotency\Support\ProcessingOwner;
use Tempest\Idempotency\Support\ProcessingOwnerLiveness;
use Tempest\Reflection\ClassReflector;
use Tempest\Support\Priority;
use Throwable;

#[Priority(Priority::FRAMEWORK - 1)]
final readonly class IdempotentCommandMiddleware implements CommandBusMiddleware
{
    public function __construct(
        private Cache $cache,
        private IdempotencyStore $store,
        private IdempotencyKeyResolver $keyResolver,
        private CommandFingerprintGenerator $fingerprintGenerator,
        private IdempotencyConfig $config,
        private CommandBusConfig $commandBusConfig,
        private ProcessingOwner $processingOwner,
    ) {}

    public function __invoke(object $command, CommandBusMiddlewareCallable $next): void
    {
        $options = $this->resolveOptions($command);

        if ($options === null) {
            $next($command);

            return;
        }

        if (PHP_OS_FAMILY === 'Windows') {
            throw IdempotencyPlatformWasNotSupported::forWindows();
        }

        $scope = $this->resolveScope($command);
        $fingerprint = $this->fingerprintGenerator->generate($command);
        $key = $this->resolveKey($command, $fingerprint);
        $pendingTtlInSeconds = $options['pendingTtlInSeconds'];
        $pendingRecordTtlInSeconds = max($options['ttlInSeconds'], $pendingTtlInSeconds);
        $owner = $this->processingOwner->create();

        $lock = $this->cache->lock(
            key: $this->keyResolver->lockKey($scope, $key),
            duration: Duration::seconds($pendingTtlInSeconds),
            owner: $owner,
        );

        $lock->execute(
            function () use ($scope, $key, $fingerprint, $options, $pendingRecordTtlInSeconds, $owner, $next, $command): void {
                $record = $this->store->find($scope, $key);

                if ($record instanceof IdempotencyRecord) {
                    if (! $this->shouldTakeOverPendingRecord($record, $fingerprint, $scope, $key, $options['pendingTtlInSeconds'])) {
                        return;
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

                $heartbeat = new HeartbeatRenewer();
                $heartbeat->start(
                    store: $this->store,
                    scope: $scope,
                    key: $key,
                    owner: $owner,
                    intervalInSeconds: max(1, intdiv($options['pendingTtlInSeconds'], 3)),
                    recordTtlInSeconds: $pendingRecordTtlInSeconds,
                );

                try {
                    $next($command);

                    $this->store->saveCompleted(
                        scope: $scope,
                        key: $key,
                        fingerprint: $fingerprint,
                        response: null,
                        ttlInSeconds: $options['ttlInSeconds'],
                    );
                } catch (Throwable $throwable) {
                    $this->store->delete($scope, $key);

                    throw $throwable;
                } finally {
                    $heartbeat->stop();
                }
            },
            wait: Duration::seconds($pendingTtlInSeconds),
        );
    }

    private function assertRecordMatches(IdempotencyRecord $record, string $fingerprint, string $scope, string $key): void
    {
        if ($record->fingerprint !== $fingerprint) {
            throw IdempotencyKeyWasAlreadyUsed::forScope($scope, $key);
        }
    }

    private function resolveKey(object $command, string $fingerprint): string
    {
        if (! $command instanceof HasIdempotencyKey) {
            return $fingerprint;
        }

        $key = trim($command->getIdempotencyKey());

        if ($key === '') {
            return $fingerprint;
        }

        return $key;
    }

    /** @return null|array{ttlInSeconds: int, pendingTtlInSeconds: int} */
    private function resolveOptions(object $command): ?array
    {
        $instance = new ClassReflector($command)->getAttribute(Idempotent::class);

        if ($instance === null) {
            $instance = ($this->commandBusConfig->handlers[$command::class] ?? null)
                ?->handler
                ->getAttribute(Idempotent::class);
        }

        if ($instance === null) {
            return null;
        }

        return [
            'ttlInSeconds' => $instance->ttlInSeconds ?? $this->config->ttlInSeconds,
            'pendingTtlInSeconds' => $instance->pendingTtlInSeconds ?? $this->config->pendingTtlInSeconds,
        ];
    }

    private function shouldTakeOverPendingRecord(IdempotencyRecord $record, string $fingerprint, string $scope, string $key, int $pendingTtlInSeconds): bool
    {
        $this->assertRecordMatches($record, $fingerprint, $scope, $key);

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

    private function resolveScope(object $command): string
    {
        return 'command:' . $command::class;
    }
}
