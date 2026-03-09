<?php

namespace Tempest\KeyValue\Redis;

use Exception;
use Predis;

final class RedisExtensionWasMissing extends Exception implements RedisException
{
    public function __construct(string $fqcn)
    {
        parent::__construct(
            'Redis client not found.' . match ($fqcn) {
                \Redis::class => ' You may be missing the `redis` extension.',
                Predis\Client::class => ' You may need to install the `predis/predis` package.',
                default => ' Install the `redis` extension or the `predis/predis` package.',
            },
        );
    }
}
