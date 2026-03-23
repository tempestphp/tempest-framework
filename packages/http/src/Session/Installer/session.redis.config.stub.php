<?php

use Tempest\DateTime\Duration;
use Tempest\Http\Session\Config\RedisSessionConfig;

return new RedisSessionConfig(
    expiration: Duration::days(30),
);