<?php

declare(strict_types=1);

namespace Tempest\Config;

use Tempest;
use Tempest\Log\Config\DailyLogConfig;

return new DailyLogConfig(
    path: Tempest\internal_storage_path('logs', 'tempest.log'),
    prefix: Tempest\env('APPLICATION_NAME', default: 'tempest'),
    maxFiles: Tempest\env('LOG_MAX_FILES', default: 31),
);
