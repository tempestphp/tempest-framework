<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\CommandBus;

use PHPUnit\Framework\Attributes\PostCondition;
use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;
use Tempest\CommandBus\AsyncCommandRepositories\RedisCommandRepository;
use Tempest\CommandBus\Exceptions\PendingCommandCouldNotBeResolved;
use Tempest\KeyValue\Redis\Config\RedisConfig;
use Tempest\KeyValue\Redis\Redis;
use Tests\Tempest\Fixtures\Commands\MyCommand;
use Tests\Tempest\Integration\FrameworkIntegrationTestCase;
use Throwable;

use function Tempest\Support\Random\uuid;

/**
 * @internal
 */
final class RedisCommandRepositoryTest extends FrameworkIntegrationTestCase
{
    private Redis $redis;

    private RedisCommandRepository $repository;

    #[PreCondition]
    protected function configure(): void
    {
        $this->eventBus->preventEventHandling();

        $this->container->config(new RedisConfig(
            prefix: 'tempest_test:',
            database: 6,
            connectionTimeOut: .2,
        ));

        $this->redis = $this->container->get(Redis::class);
        $this->repository = $this->container->get(RedisCommandRepository::class);

        try {
            $this->redis->connect();
        } catch (Throwable) {
            $this->markTestSkipped('Could not connect to Redis.');
        }
    }

    #[PostCondition]
    protected function cleanup(): void
    {
        try {
            $this->redis->flush();
        } catch (Throwable) { // @mago-expect lint:no-empty-catch-clause
        }
    }

    #[Test]
    public function store_and_retrieve(): void
    {
        $command = new MyCommand();

        $this->repository->store($uuid = uuid(), $command);

        $pending = $this->repository->getPendingCommands();
        $this->assertArrayHasKey($uuid, $pending);
        $this->assertEquals($command, $pending[$uuid]);
        $this->assertEquals($command, $this->repository->findPendingCommand($uuid));

        $this->repository->markAsFailed($uuid);
        $this->assertArrayNotHasKey($uuid, $this->repository->getPendingCommands());

        $this->expectException(PendingCommandCouldNotBeResolved::class);
        $this->repository->findPendingCommand($uuid);
    }

    #[Test]
    public function marking_as_done_removes_record(): void
    {
        $this->repository->store($uuid = uuid(), new MyCommand());
        $this->assertArrayHasKey($uuid, $this->repository->getPendingCommands());

        $this->repository->markAsDone($uuid);

        $this->assertArrayNotHasKey($uuid, $this->repository->getPendingCommands());
    }

    #[Test]
    public function cant_find_not_stored_command(): void
    {
        $this->expectException(PendingCommandCouldNotBeResolved::class);
        $this->repository->findPendingCommand(uuid());
    }

    #[Test]
    public function stores_all_pending_commands_in_a_single_hash(): void
    {
        $this->repository->store($first = uuid(), new MyCommand());
        $this->repository->store($second = uuid(), new MyCommand());

        $this->assertSame(2, (int) $this->redis->command('HLEN', 'command:pending'));

        // `HSCAN` does not promise any order once the hash grows past a certain size
        $this->assertEqualsCanonicalizing([$first, $second], array_keys($this->repository->getPendingCommands()));
    }

    #[Test]
    public function retrieves_a_backlog_larger_than_a_single_scan_batch(): void
    {
        $uuids = [];

        // Redis treats `COUNT` as a hint, so this just needs to be well above the batch size to
        // make sure the scan takes more than one round
        for ($i = 0; $i < 1_200; $i++) {
            $this->repository->store($uuids[] = uuid(), new MyCommand());
        }

        $this->assertEqualsCanonicalizing($uuids, array_keys($this->repository->getPendingCommands()));
    }

    #[Test]
    public function marking_as_failed_moves_the_command_to_the_failed_hash(): void
    {
        $this->repository->store($uuid = uuid(), $command = new MyCommand());
        $this->repository->markAsFailed($uuid);

        $this->assertSame(0, (int) $this->redis->command('HEXISTS', 'command:pending', $uuid));
        $this->assertEquals($command, unserialize($this->redis->command('HGET', 'command:failed', $uuid)));
    }

    #[Test]
    public function marking_an_unknown_command_as_failed_does_nothing(): void
    {
        $this->repository->markAsFailed(uuid());

        $this->assertSame(0, (int) $this->redis->command('EXISTS', 'command:failed'));
    }

    #[Test]
    public function skips_commands_that_cannot_be_unserialized(): void
    {
        $this->redis->command('HSET', 'command:pending', $corrupted = uuid(), 'not-a-serialized-command');
        $this->repository->store($uuid = uuid(), new MyCommand());

        $pending = $this->repository->getPendingCommands();

        $this->assertArrayHasKey($uuid, $pending);
        $this->assertArrayNotHasKey($corrupted, $pending);
    }

    #[Test]
    public function cant_find_a_command_that_cannot_be_unserialized(): void
    {
        $this->redis->command('HSET', 'command:pending', $uuid = uuid(), 'not-a-serialized-command');

        $this->expectException(PendingCommandCouldNotBeResolved::class);
        $this->repository->findPendingCommand($uuid);
    }
}
