<?php

namespace Tempest\KeyValue\Redis\Config;

use UnitEnum;

final class RedisConfig
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
         * Specifies the protocol used to communicate with the Redis instance. This is specific to predis.
         */
        public RedisConnectionScheme $scheme = RedisConnectionScheme::TCP,

        /**
         * Specifies if the underlying connection resource should be left open when a script ends its lifecycle.
         */
        public bool $persistent = false,

        /**
         * The maximum duration, in seconds, to wait for a connection to be established.
         */
        public ?float $connectionTimeOut = null,

        /**
         * Less common Predis or PhpRedis client options.
         */
        public array $options = [],

        /**
         * Less common connection options.
         */
        public array $connection = [],
    ) {}
}
