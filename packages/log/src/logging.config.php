<?php

declare(strict_types=1);

namespace Tempest\Config;

use Tempest;
use Tempest\Log\Channels\DailyLogChannel;
use Tempest\Log\LogConfig;

return new LogConfig(
    channels: [
        new DailyLogChannel(
            path: Tempest\internal_storage_path('logs', 'tempest.log'),
            maxFiles: Tempest\env('LOG_MAX_FILES', default: 31),
        ),
    ],
);
