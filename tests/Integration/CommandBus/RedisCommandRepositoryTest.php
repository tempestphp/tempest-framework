<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\CommandBus;

use PHPUnit\Framework\Attributes\PostCondition;
use PHPUnit\Framework\Attributes\PreCondition;
use PHPUnit\Framework\Attributes\Test;
use Tempest\CommandBus\AsyncCommandRepositories\RedisCommandRepository;
use Tempest\CommandBus\Exceptions\PendingCommandCouldNotBeResolved;
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
    #[PreCondition]
    protected function configure(): void
    {
        try {
            $this->container->get(Redis::class)->connect();
        } catch (Throwable) {
            $this->markTestSkipped('Could not connect to Redis.');
        }
    }

    #[PostCondition]
    protected function cleanup(): void
    {
        try {
            $this->container->get(Redis::class)->flush();
        } catch (Throwable) { // @mago-expect lint:no-empty-catch-clause
        }
    }

    #[Test]
    public function store_and_retrieve(): void
    {
        $repository = $this->container->get(RedisCommandRepository::class);
        $command = new MyCommand();

        $repository->store($uuid = uuid(), $command);

        $pending = $repository->getPendingCommands();
        $this->assertArrayHasKey($uuid, $pending);
        $this->assertEquals($command, $pending[$uuid]);
        $this->assertEquals($command, $repository->findPendingCommand($uuid));

        $repository->markAsFailed($uuid);
        $this->assertArrayNotHasKey($uuid, $repository->getPendingCommands());

        $this->expectException(PendingCommandCouldNotBeResolved::class);
        $repository->findPendingCommand($uuid);
    }

    #[Test]
    public function marking_as_done_removes_record(): void
    {
        $repository = $this->container->get(RedisCommandRepository::class);
        $command = new MyCommand();

        $repository->store($uuid = uuid(), $command);
        $pending = $repository->getPendingCommands();
        $this->assertArrayHasKey($uuid, $pending);

        $repository->markAsDone($uuid);

        $this->assertArrayNotHasKey($uuid, $repository->getPendingCommands());
    }

    #[Test]
    public function cant_find_not_stored_command(): void
    {
        $repository = $this->container->get(RedisCommandRepository::class);

        $uuid = uuid();

        $this->expectException(PendingCommandCouldNotBeResolved::class);
        $repository->findPendingCommand($uuid);
    }

    #[Test]
    public function stores_all_pending_commands_in_a_single_hash(): void
    {
        $repository = $this->container->get(RedisCommandRepository::class);
        $redis = $this->container->get(Redis::class);

        $repository->store($first = uuid(), new MyCommand());
        $repository->store($second = uuid(), new MyCommand());

        $this->assertSame(2, (int) $redis->command('HLEN', 'command:pending'));

        // `HSCAN` does not promise any order once the hash grows past a certain size
        $this->assertEqualsCanonicalizing([$first, $second], array_keys($repository->getPendingCommands()));
    }

    #[Test]
    public function retrieves_a_backlog_larger_than_a_single_scan_batch(): void
    {
        $repository = $this->container->get(RedisCommandRepository::class);

        $uuids = [];

        // Redis treats `COUNT` as a hint, so this just needs to be well above the batch size to
        // make sure the scan takes more than one round
        for ($i = 0; $i < 1_200; $i++) {
            $repository->store($uuids[] = uuid(), new MyCommand());
        }

        $this->assertEqualsCanonicalizing($uuids, array_keys($repository->getPendingCommands()));
    }

    #[Test]
    public function marking_as_failed_moves_the_command_to_the_failed_hash(): void
    {
        $repository = $this->container->get(RedisCommandRepository::class);
        $redis = $this->container->get(Redis::class);

        $repository->store($uuid = uuid(), $command = new MyCommand());
        $repository->markAsFailed($uuid);

        $this->assertSame(0, (int) $redis->command('HEXISTS', 'command:pending', $uuid));
        $this->assertEquals($command, unserialize($redis->command('HGET', 'command:failed', $uuid)));
    }

    #[Test]
    public function marking_an_unknown_command_as_failed_does_nothing(): void
    {
        $repository = $this->container->get(RedisCommandRepository::class);
        $redis = $this->container->get(Redis::class);

        $repository->markAsFailed(uuid());

        $this->assertSame(0, (int) $redis->command('EXISTS', 'command:failed'));
    }

    #[Test]
    public function ignores_unrelated_keys(): void
    {
        $repository = $this->container->get(RedisCommandRepository::class);
        $redis = $this->container->get(Redis::class);

        $redis->set('cache:unrelated', 'value');
        $repository->store($uuid = uuid(), new MyCommand());

        $this->assertSame([$uuid], array_keys($repository->getPendingCommands()));
        $this->assertSame('value', $redis->get('cache:unrelated'));
    }

    #[Test]
    public function skips_commands_that_cannot_be_unserialized(): void
    {
        $repository = $this->container->get(RedisCommandRepository::class);
        $redis = $this->container->get(Redis::class);

        $redis->command('HSET', 'command:pending', $corrupted = uuid(), 'not-a-serialized-command');
        $repository->store($uuid = uuid(), new MyCommand());

        $pending = $repository->getPendingCommands();

        $this->assertArrayHasKey($uuid, $pending);
        $this->assertArrayNotHasKey($corrupted, $pending);
    }

    #[Test]
    public function cant_find_a_command_that_cannot_be_unserialized(): void
    {
        $repository = $this->container->get(RedisCommandRepository::class);
        $redis = $this->container->get(Redis::class);

        $redis->command('HSET', 'command:pending', $uuid = uuid(), 'not-a-serialized-command');

        $this->expectException(PendingCommandCouldNotBeResolved::class);
        $repository->findPendingCommand($uuid);
    }
}
