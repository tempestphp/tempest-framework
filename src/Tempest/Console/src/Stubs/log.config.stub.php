<?php

declare(strict_types=1);

use Tempest\Log\Channels\AppendLogChannel;
use Tempest\Log\LogConfig;

return new LogConfig(
    channels: [
        new AppendLogChannel(
            path: __DIR__ . '/../logs/project.log',
        ),
    ],
    serverLogPath: '/path/to/nginx.log',
);
