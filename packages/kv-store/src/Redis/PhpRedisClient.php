<?php

namespace Tempest\KeyValue\Redis;

use Stringable;
use Tempest\DateTime\DateTime;
use Tempest\DateTime\DateTimeInterface;
use Tempest\DateTime\Duration;
use Tempest\EventBus\EventBus;
use Tempest\KeyValue\Redis\Config\RedisConfig;
use Tempest\Support\Arr\ArrayInterface;

use function Tempest\Support\Arr\is_associative;

final readonly class PhpRedisClient implements Redis
{
    public function __construct(
        private \Redis $client,
        private RedisConfig $config,
        private ?EventBus $eventBus = null,
    ) {}

    public function connect(): void
    {
        if ($this->client->isConnected()) {
            return;
        }

        if ($this->config->persistent) {
            $this->client->pconnect(
                host: $this->config->unixSocketPath ?? $this->config->host ?? '127.0.0.1',
                port: $this->config->port ?? 6379,
                timeout: $this->config->connectionTimeOut ?? 0,
                persistent_id: $this->config->persistentId,
            );
        } else {
            $this->client->connect(
                host: $this->config->unixSocketPath ?? $this->config->host ?? '127.0.0.1',
                port: $this->config->port ?? 6379,
                timeout: $this->config->connectionTimeOut ?? 0,
            );
        }

        if ($this->config->username || $this->config->password) {
            $this->client->auth([
                'user' => $this->config->username,
                'pass' => $this->config->password,
            ]);
        }

        if ($this->config->prefix) {
            $this->client->setOption(\Redis::OPT_PREFIX, $this->config->prefix);
        }

        if ($this->config->database) {
            $this->client->select($this->config->database);
        }

        if ($this->config->options && ! is_associative($this->config->options)) {
            throw new \InvalidArgumentException('The `options` property of the Redis configuration must be an associative array.');
        }

        foreach ($this->config->options as $key => $value) {
            $this->client->setOption($key, $value);
        }
    }

    public function disconnect(): void
    {
        $this->client->close();
    }

    public function flush(): void
    {
        $this->client->flushdb();
    }

    public function command(Stringable|string $command, Stringable|string ...$arguments): mixed
    {
        if (! $this->client->isConnected()) {
            $this->connect();
        }

        $command = (string) $command;
        $arguments = array_map(fn (Stringable|string $argument) => (string) $argument, $arguments);
        $startedAt = DateTime::now();
        $result = $this->client->rawcommand($command, ...$arguments);

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

    public function getClient(): \Redis
    {
        if (! $this->client->isConnected()) {
            $this->connect();
        }

        return $this->client;
    }
}
