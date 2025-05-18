<?php

namespace Tempest\KeyValue\Redis\Config;

use UnitEnum;

final class PhpRedisConfig implements RedisConfig
{
    public function __construct(
        /**
         * Add a prefix to all keys in the Redis database.
         */
        public ?string $prefix = null,

        /**
         * Username to the Redis instance.
         */
        public ?string $username = null,

        /**
         * Password to the Redis instance.
         */
        public ?string $password = null,

        /**
         * IP or hostname of the target server. This is ignored when connecting to Redis using UNIX domain sockets.
         */
        public ?string $host = null,

        /**
         * TCP/IP port of the target server. This is ignored when connecting to Redis using UNIX domain sockets.
         */
        public ?int $port = null,

        /**
         * Logical database to connect to.
         */
        public ?int $database = null,

        /**
         * Path of the UNIX domain socket file used when connecting to Redis using UNIX domain sockets.
         */
        public ?int $unixSocketPath = null,

        /**
         * Specifies if the underlying connection resource should be left open when a script ends its lifecycle.
         */
        public bool $persistent = false,

        /**
         * A unique identifier for the connection resource.
         */
        public ?string $persistentId = null,

        /**
         * The maximum duration, in seconds, to wait for a connection to be established.
         */
        public ?float $connectionTimeOut = null,

        /**
         * Less common Redis client options.
         */
        public array $options = [],

        /**
         * Identifier for this key-value store configuration.
         */
        public null|string|UnitEnum $tag = null,
    ) {}
}
