<?php

use Tempest\DateTime\Duration;
use Tempest\Http\Session\Config\FileSessionConfig;

return new FileSessionConfig(
    expiration: Duration::days(30),
    path: 'sessions',
);
