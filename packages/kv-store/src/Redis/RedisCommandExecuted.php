<?php

namespace Tempest\KeyValue\Redis;

use Tempest\DateTime\Duration;

final class RedisCommandExecuted
{
    public function __construct(
        public string $command,
        public array $arguments,
        public Duration $duration,
        public mixed $result,
    ) {}
}
