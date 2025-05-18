<?php

namespace Tempest\KeyValue\Redis;

use Exception;
use Predis\Client;

final class MissingRedisException extends Exception implements RedisException
{
    public function __construct(string $fqcn)
    {
        parent::__construct(
            'Redis client not found.' .
                match ($fqcn) {
                    \Redis::class => ' You may be missing the `redis` extension.',
                    Client::class => ' You may need to install the `predis/predis` package.',
                },
        );
    }
}
