<?php

declare(strict_types=1);

namespace Tempest\Idempotency\Tests;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Tempest\Cache\GenericCache;
use Tempest\CommandBus\CommandBusConfig;
use Tempest\CommandBus\CommandBusMiddlewareCallable;
use Tempest\CommandBus\CommandHandler;
use Tempest\Idempotency\Attributes\Idempotent;
use Tempest\Idempotency\Config\IdempotencyConfig;
use Tempest\Idempotency\Exceptions\IdempotencyKeyWasAlreadyUsed;
use Tempest\Idempotency\Fingerprint\ObjectFingerprintGenerator;
use Tempest\Idempotency\HasIdempotencyKey;
use Tempest\Idempotency\Middleware\IdempotentCommandMiddleware;
use Tempest\Idempotency\Store\CacheIdempotencyStore;
use Tempest\Idempotency\Support\IdempotencyKeyResolver;
use Tempest\Idempotency\Support\ProcessingOwner;
use Tempest\Idempotency\Tests\Fixtures\RecordingCache;
use Tempest\Idempotency\Tests\Fixtures\RecordingStore;
use Tempest\Reflection\MethodReflector;

final class IdempotentCommandMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Idempotency tests are not supported on Windows.');
        }
    }

    #[Test]
    public function ignores_commands_without_idempotent_attribute(): void
    {
        $middleware = $this->createMiddleware();
        $calls = 0;
        $command = new CreateDraftCommand('A');

        $next = new CommandBusMiddlewareCallable(function (object $_) use (&$calls): void {
            $calls++;
        });

        $middleware($command, $next);
        $middleware($command, $next);

        $this->assertSame(2, $calls);
    }

    #[Test]
    public function supports_idempotent_attribute_on_handler_method(): void
    {
        $commandBusConfig = new CommandBusConfig();
        $handler = new CommandHandler();
        $commandBusConfig->addHandler(
            $handler,
            SyncInventoryCommand::class,
            MethodReflector::fromParts(SyncInventoryHandler::class, 'handle'),
        );

        $middleware = $this->createMiddleware($commandBusConfig);
        $calls = 0;
        $command = new SyncInventoryCommand(warehouse: 'east', sku: 'WIDGET-1');

        $next = new CommandBusMiddlewareCallable(function (object $_) use (&$calls): void {
            $calls++;
        });

        $middleware($command, $next);
        $middleware($command, $next);

        $this->assertSame(1, $calls);
    }

    #[Test]
    public function executes_an_idempotent_command_only_once_for_equal_payloads(): void
    {
        $middleware = $this->createMiddleware();
        $calls = 0;
        $command = new ImportInvoicesCommand(tenant: 'acme', month: '2026-01');

        $next = new CommandBusMiddlewareCallable(function (object $_) use (&$calls): void {
            $calls++;
        });

        $middleware($command, $next);
        $middleware($command, $next);

        $this->assertSame(1, $calls);
    }

    #[Test]
    public function throws_when_the_same_explicit_key_is_used_for_different_payloads(): void
    {
        $middleware = $this->createMiddleware();
        $calls = 0;

        $next = new CommandBusMiddlewareCallable(function (object $_) use (&$calls): void {
            $calls++;
        });

        $middleware(new CreatePayoutCommand('payout-1', 100), $next);

        $this->expectException(IdempotencyKeyWasAlreadyUsed::class);

        $middleware(new CreatePayoutCommand('payout-1', 101), $next);
    }

    #[Test]
    public function allows_replay_for_same_explicit_key_and_same_payload(): void
    {
        $middleware = $this->createMiddleware();
        $calls = 0;
        $command = new CreatePayoutCommand('payout-1', 100);

        $next = new CommandBusMiddlewareCallable(function (object $_) use (&$calls): void {
            $calls++;
        });

        $middleware($command, $next);
        $middleware($command, $next);

        $this->assertSame(1, $calls);
    }

    #[Test]
    public function uses_pending_ttl_for_lock_and_completion_ttl_for_pending_record(): void
    {
        $cache = new RecordingCache();
        $config = new IdempotencyConfig(ttlInSeconds: 120, pendingTtlInSeconds: 5);
        $resolver = new IdempotencyKeyResolver($config);
        $store = new RecordingStore($cache, $resolver);
        $middleware = new IdempotentCommandMiddleware(
            cache: $cache,
            store: $store,
            keyResolver: $resolver,
            fingerprintGenerator: new ObjectFingerprintGenerator(),
            config: $config,
            commandBusConfig: new CommandBusConfig(),
            processingOwner: new ProcessingOwner(),
        );

        $middleware(
            new ImportInvoicesCommand(tenant: 'acme', month: '2026-01'),
            new CommandBusMiddlewareCallable(static function (object $_): void {}),
        );

        $this->assertSame(5.0, $cache->lastLockDuration?->getTotalSeconds());
        $this->assertSame(120, $store->lastSavePending['ttlInSeconds'] ?? null);
        $this->assertIsString($store->lastSavePending['pendingOwner'] ?? null);
        $this->assertIsInt($store->lastSavePending['pendingHeartbeatAt'] ?? null);
    }

    #[Test]
    public function takes_over_a_pending_record_owned_by_a_dead_process(): void
    {
        $cache = new GenericCache(new ArrayAdapter());
        $config = new IdempotencyConfig();
        $resolver = new IdempotencyKeyResolver($config);
        $store = new CacheIdempotencyStore($cache, $resolver);
        $command = new ImportInvoicesCommand(tenant: 'acme', month: '2026-01');
        $fingerprintGenerator = new ObjectFingerprintGenerator();
        $fingerprint = $fingerprintGenerator->generate($command);
        $middleware = new IdempotentCommandMiddleware(
            cache: $cache,
            store: $store,
            keyResolver: $resolver,
            fingerprintGenerator: $fingerprintGenerator,
            config: $config,
            commandBusConfig: new CommandBusConfig(),
            processingOwner: new ProcessingOwner(),
        );

        $store->savePending(
            scope: 'command:' . $command::class,
            key: $fingerprint,
            fingerprint: $fingerprint,
            ttlInSeconds: 120,
            pendingOwner: sprintf('%s|%d|%s', php_uname('n'), 99_999_999, 'stale-owner'),
            pendingHeartbeatAt: time(),
        );

        $calls = 0;
        $middleware(
            $command,
            new CommandBusMiddlewareCallable(function (object $_) use (&$calls): void {
                $calls++;
            }),
        );

        $this->assertSame(1, $calls);
    }

    #[Test]
    public function takes_over_a_pending_record_owned_by_another_host_when_the_heartbeat_is_stale(): void
    {
        $cache = new GenericCache(new ArrayAdapter());
        $config = new IdempotencyConfig();
        $resolver = new IdempotencyKeyResolver($config);
        $store = new CacheIdempotencyStore($cache, $resolver);
        $command = new ImportInvoicesCommand(tenant: 'acme', month: '2026-01');
        $fingerprintGenerator = new ObjectFingerprintGenerator();
        $fingerprint = $fingerprintGenerator->generate($command);

        $middleware = new IdempotentCommandMiddleware(
            cache: $cache,
            store: $store,
            keyResolver: $resolver,
            fingerprintGenerator: $fingerprintGenerator,
            config: $config,
            commandBusConfig: new CommandBusConfig(),
            processingOwner: new ProcessingOwner(),
        );

        $store->savePending(
            scope: 'command:' . $command::class,
            key: $fingerprint,
            fingerprint: $fingerprint,
            ttlInSeconds: 120,
            pendingOwner: 'remote-host|12345|stale-owner',
            pendingHeartbeatAt: time() - 120,
        );

        $calls = 0;
        $middleware(
            $command,
            new CommandBusMiddlewareCallable(function (object $_) use (&$calls): void {
                $calls++;
            }),
        );

        $this->assertSame(1, $calls);
    }

    #[Test]
    public function does_not_take_over_a_pending_record_owned_by_another_host_when_the_heartbeat_is_fresh(): void
    {
        $cache = new GenericCache(new ArrayAdapter());
        $config = new IdempotencyConfig();
        $resolver = new IdempotencyKeyResolver($config);
        $store = new CacheIdempotencyStore($cache, $resolver);
        $command = new ImportInvoicesCommand(tenant: 'acme', month: '2026-01');
        $fingerprintGenerator = new ObjectFingerprintGenerator();
        $fingerprint = $fingerprintGenerator->generate($command);

        $middleware = new IdempotentCommandMiddleware(
            cache: $cache,
            store: $store,
            keyResolver: $resolver,
            fingerprintGenerator: $fingerprintGenerator,
            config: $config,
            commandBusConfig: new CommandBusConfig(),
            processingOwner: new ProcessingOwner(),
        );

        $store->savePending(
            scope: 'command:' . $command::class,
            key: $fingerprint,
            fingerprint: $fingerprint,
            ttlInSeconds: 120,
            pendingOwner: 'remote-host|12345|alive-owner',
            pendingHeartbeatAt: time(),
        );

        $calls = 0;
        $middleware(
            $command,
            new CommandBusMiddlewareCallable(function (object $_) use (&$calls): void {
                $calls++;
            }),
        );

        $this->assertSame(0, $calls);
    }

    private function createMiddleware(?CommandBusConfig $commandBusConfig = null): IdempotentCommandMiddleware
    {
        $cache = new GenericCache(new ArrayAdapter());
        $config = new IdempotencyConfig();
        $resolver = new IdempotencyKeyResolver($config);

        return new IdempotentCommandMiddleware(
            cache: $cache,
            store: new CacheIdempotencyStore($cache, $resolver),
            keyResolver: $resolver,
            fingerprintGenerator: new ObjectFingerprintGenerator(),
            config: $config,
            commandBusConfig: $commandBusConfig ?? new CommandBusConfig(),
            processingOwner: new ProcessingOwner(),
        );
    }
}

final readonly class CreateDraftCommand
{
    public function __construct(
        public string $title,
    ) {}
}

#[Idempotent]
final readonly class ImportInvoicesCommand
{
    public function __construct(
        public string $tenant,
        public string $month,
    ) {}
}

#[Idempotent]
final readonly class CreatePayoutCommand implements HasIdempotencyKey
{
    public function __construct(
        public string $idempotencyKey,
        public int $amount,
    ) {}

    public function getIdempotencyKey(): string
    {
        return $this->idempotencyKey;
    }
}

final readonly class SyncInventoryCommand
{
    public function __construct(
        public string $warehouse,
        public string $sku,
    ) {}
}

final class SyncInventoryHandler
{
    #[Idempotent]
    #[CommandHandler]
    public function handle(SyncInventoryCommand $command): void {}
}
