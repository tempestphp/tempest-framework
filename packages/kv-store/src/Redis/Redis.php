<?php

namespace Tempest\KeyValue\Redis;

use Stringable;
use Tempest\DateTime\DateTimeInterface;
use Tempest\DateTime\Duration;

interface Redis
{
    /**
     * Get the underlying client. This breaks abstraction and should only be used if absolutely necessary.
     */
    public function getClient(): \Redis|\Predis\Client;

    /**
     * Flushes the Redis database.
     */
    public function flush(): void;

    /**
     * Disconnects from the Redis database.
     */
    public function disconnect(): void;

    /**
     * Connects to the Redis database. This is done under the hood when calling any command.
     */
    public function connect(): void;

    /**
     * Executes a raw command against the Redis database.
     */
    public function command(Stringable|string $command, Stringable|string ...$arguments): mixed;

    /**
     * Sets the given key/value pair, with an optional expiration.
     */
    public function set(Stringable|string $key, mixed $value, null|Duration|DateTimeInterface $expiration = null): void;

    /**
     * Gets the value for the given key.
     */
    public function get(Stringable|string $key): mixed;
}
