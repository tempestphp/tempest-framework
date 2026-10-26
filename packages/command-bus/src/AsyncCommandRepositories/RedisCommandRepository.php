<?php

declare(strict_types=1);

namespace Tempest\CommandBus\AsyncCommandRepositories;

use Deprecated;
use Tempest\CommandBus\CommandRepository;
use Tempest\CommandBus\Exceptions\PendingCommandCouldNotBeResolved;
use Tempest\KeyValue\Redis\Redis;
use Throwable;

final readonly class RedisCommandRepository implements CommandRepository
{
    private const string PENDING_KEY = 'command:pending';

    private const string FAILED_KEY = 'command:failed';

    /**
     * Set once the old one key per command layout has been migrated, so the keyspace is scanned once.
     */
    #[Deprecated(message: 'Remove in 4.0, along with the key itself.')]
    private const string MIGRATION_KEY = 'command:migrated';

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

    /**
     * Moves one command from its own key into the matching hash, as a script so it ends up in exactly one.
     */
    #[Deprecated(message: 'Remove in 4.0.')]
    private const string MIGRATE_COMMAND_SCRIPT = <<<'LUA'
    local command = redis.call('GET', KEYS[1])

    if not command then
        return 0
    end

    redis.call('HSET', KEYS[2], ARGV[1], command)
    redis.call('UNLINK', KEYS[1])

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
     * Moves commands that earlier versions stored under their own key into the hashes, and returns how many.
     */
    #[Deprecated(message: 'Remove in 4.0.')]
    public function migrateStoredCommands(): int
    {
        if ((int) $this->redis->command('EXISTS', self::MIGRATION_KEY) === 1) {
            return 0;
        }

        $migrated = $this->migrateLegacyKeys(self::PENDING_KEY) + $this->migrateLegacyKeys(self::FAILED_KEY);

        $this->redis->command('SET', self::MIGRATION_KEY, '1');

        return $migrated;
    }

    /**
     * Scans from the client, since Redis before 7 refuses to write after a `SCAN`.
     */
    #[Deprecated(message: 'Remove in 4.0.')]
    private function migrateLegacyKeys(string $hash): int
    {
        $prefix = $hash . ':';
        $migrated = 0;
        $cursor = '0';

        do {
            $response = $this->redis->command('SCAN', $cursor, 'MATCH', $prefix . '*', 'COUNT', self::SCAN_COUNT);

            if (! is_array($response) || count($response) !== 2) {
                return $migrated;
            }

            [$cursor, $keys] = $response;

            foreach (is_array($keys) ? $keys : [] as $key) {
                $uuid = substr((string) $key, strlen($prefix));

                $migrated += (int) $this->redis->command('EVAL', self::MIGRATE_COMMAND_SCRIPT, '2', (string) $key, $hash, $uuid);
            }
        } while ((string) $cursor !== '0');

        return $migrated;
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
