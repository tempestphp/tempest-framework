<?php

namespace Tempest\KeyValue\Redis;

use Predis\Client;
use Stringable;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\DateTimeInterface;
use Tempest\DateTime\Duration;
use Tempest\EventBus\EventBus;
use Tempest\Support\Arr\ArrayInterface;

final readonly class PredisClient implements Redis
{
    public function __construct(
        private Client $client,
        private ?EventBus $eventBus = null,
    ) {}

    public function connect(): void
    {
        if ($this->client->isConnected()) {
            return;
        }

        $this->client->connect();
    }

    public function disconnect(): void
    {
        $this->client->disconnect();
    }

    public function flush(): void
    {
        $this->client->flushdb();
    }

    public function command(Stringable|string $command, Stringable|string ...$arguments): mixed
    {
        $command = (string) $command;
        $arguments = array_map(fn (Stringable|string $argument) => (string) $argument, $arguments);
        $startedAt = DateTime::now();
        $result = $this->client->executeRaw(array_merge([$command], $arguments));

        $this->eventBus?->dispatch(new RedisCommandExecuted(
            command: $command,
            arguments: $arguments,
            duration: DateTime::now()->since($startedAt),
            result: $result,
        ));

        return $result;
    }

    public function set(Stringable|string $key, mixed $value, null|Duration|DateTimeInterface $expiration = null): void
    {
        if ($expiration instanceof DateTimeInterface) {
            $expiration = DateTime::now()->since($expiration);
        }

        if ($expiration?->isNegative()) {
            throw new InvalidTimeToLiveException($expiration);
        }

        $this->command('SET', ...array_filter([
            (string) $key, // key
            $this->serializeValue($value), // value
            $expiration ? 'PX' : null, // ttl format
            (int) $expiration?->getTotalMilliseconds(), // ttl
        ]));
    }

    public function get(Stringable|string $key): mixed
    {
        $value = $this->command('GET', (string) $key);

        if ($value === null) {
            return null;
        }

        if (json_validate($value)) {
            return json_decode($value, associative: true);
        }

        return $value;
    }

    private function serializeValue(mixed $value): mixed
    {
        if ($value instanceof ArrayInterface) {
            $value = $value->toArray();
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        return $value;
    }

    public function getClient(): Client
    {
        return $this->client;
    }
}
