<?php

use Tempest\KeyValue\Redis\Config\RedisConfig;

use function Tempest\env;

return new RedisConfig(
    prefix: env('REDIS_PREFIX'),
    username: env('REDIS_USERNAME'),
    password: env('REDIS_PASSWORD'),
    host: env('REDIS_HOST'),
    port: env('REDIS_PORT'),
    database: env('REDIS_DATABASE'),
);
