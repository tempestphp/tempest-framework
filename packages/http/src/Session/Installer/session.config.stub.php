<?php

use Tempest\DateTime\Duration;
use Tempest\Http\Session\Config\DatabaseSessionConfig;
use Tempest\Http\Session\Models\DatabaseSession;

return new DatabaseSessionConfig(
    expiration: Duration::days(30),
);
