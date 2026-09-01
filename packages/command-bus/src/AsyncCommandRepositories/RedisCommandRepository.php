<?php

declare(strict_types=1);

namespace Tempest\CommandBus\AsyncCommandRepositories;

use Tempest\CommandBus\CommandRepository;
use Tempest\CommandBus\Exceptions\PendingCommandCouldNotBeResolved;
use Tempest\KeyValue\Redis\Redis;
use Throwable;

final readonly class RedisCommandRepository implements CommandRepository
{
    private const string PENDING_KEY = 'command:pending';

    private const string FAILED_KEY = 'command:failed';

    /**
     * How many fields to read per `HSCAN` batch. Bigger batches mean fewer round trips but larger
     * replies; 500 measured as a good middle ground.
     */
    private const string SCAN_COUNT = '500';

    /**
     * Moves a command from the pending hash to the failed one. Runs as a script so the two writes
     * cannot be interrupted halfway.
     */
    private const string MARK_AS_FAILED_SCRIPT = <<<'LUA'
    local command = redis.call('HGET', KEYS[1], ARGV[1])

    if not command then
        return 0
    end

    redis.call('HSET', KEYS[2], ARGV[1], command)
    redis.call('HDEL', KEYS[1], ARGV[1])

    return 1
    LUA;

    public function __construct(
        private Redis $redis,
    ) {}

    public function store(string $uuid, object $command): void
    {
        $this->redis->command('HSET', self::PENDING_KEY, $uuid, serialize($command));
    }

    public function getPendingCommands(): array
    {
        $commands = [];

        foreach ($this->scanHash(self::PENDING_KEY) as $uuid => $payload) {
            $command = $this->unserializeCommand($payload);

            if ($command === null) {
                continue;
            }

            $commands[$uuid] = $command;
        }

        return $commands;
    }

    public function findPendingCommand(string $uuid): object
    {
        $value = $this->redis->command('HGET', self::PENDING_KEY, $uuid);

        if (! is_string($value)) {
            throw new PendingCommandCouldNotBeResolved($uuid);
        }

        return $this->unserializeCommand($value) ?? throw new PendingCommandCouldNotBeResolved($uuid);
    }

    public function markAsDone(string $uuid): void
    {
        $this->redis->command('HDEL', self::PENDING_KEY, $uuid);
    }

    public function markAsFailed(string $uuid): void
    {
        $this->redis->command('EVAL', self::MARK_AS_FAILED_SCRIPT, '2', self::PENDING_KEY, self::FAILED_KEY, $uuid);
    }

    /**
     * Reads a hash in batches, instead of using `HGETALL`, which would make every other client
     * wait while Redis reads a large backlog.
     *
     * @return iterable<string, string>
     */
    private function scanHash(string $key): iterable
    {
        $cursor = '0';

        do {
            $response = $this->redis->command('HSCAN', $key, $cursor, 'COUNT', self::SCAN_COUNT);

            if (! is_array($response) || count($response) !== 2) {
                return;
            }

            [$cursor, $fields] = $response;

            yield from $this->parseHash($fields);
        } while ((string) $cursor !== '0');
    }

    /**
     * The clients pass raw commands straight through, so a hash comes back as a flat list:
     * field, value, field, value. This pairs them up again.
     *
     * @return array<string, string>
     */
    private function parseHash(mixed $fields): array
    {
        if (! is_array($fields)) {
            return [];
        }

        if (! array_is_list($fields)) {
            return $fields;
        }

        $hash = [];

        for ($i = 0; $i < (count($fields) - 1); $i += 2) {
            $hash[$fields[$i]] = $fields[$i + 1];
        }

        return $hash;
    }

    private function unserializeCommand(string $value): ?object
    {
        try {
            // A corrupted payload warns and returns `false` instead of throwing, but a custom
            // `__wakeup()` or `__unserialize()` can still throw
            $command = @unserialize($value);
        } catch (Throwable) {
            return null;
        }

        return is_object($command) ? $command : null;
    }
}
