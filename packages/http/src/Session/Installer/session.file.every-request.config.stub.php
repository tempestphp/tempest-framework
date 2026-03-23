<?php

use Tempest\DateTime\Duration;
use Tempest\Http\Session\CleanupStrategy;
use Tempest\Http\Session\Config\FileSessionConfig;

return new FileSessionConfig(
    expiration: Duration::days(30),
    cleanupStrategy: CleanupStrategy::EVERY_REQUEST,
    path: 'sessions',
);