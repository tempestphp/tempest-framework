<?php

use Tempest\Debug\DebugConfig;

return new DebugConfig(
    logPath: Tempest\internal_storage_path('logs', 'debug.log'),
);
