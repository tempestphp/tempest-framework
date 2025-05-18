<?php

namespace Tempest\KeyValue\Redis;

use Exception;
use Tempest\DateTime\Duration;

final class InvalidTimeToLiveException extends Exception implements RedisException
{
    public function __construct(Duration $duration)
    {
        parent::__construct(
            message: "The provided time to live is not valid: {$duration}.",
        );
    }
}
