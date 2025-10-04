<?php

use Tempest\DateTime\Duration;
use Tempest\Http\Session\Config\FileSessionConfig;

return new FileSessionConfig(
    path: 'sessions',
    expiration: Duration::days(30),
);
