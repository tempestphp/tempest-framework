<?php

declare(strict_types=1);

namespace Tempest\Config;

use Tempest\Log\LogConfig;

use function Tempest\env;

return new LogConfig(
    debugLogPath: env('DEBUG_LOG_PATH'),
    serverLogPath: env('SERVER_LOG_PATH'),
);
