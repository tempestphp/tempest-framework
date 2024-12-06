<?php

declare(strict_types=1);

namespace Tempest\Config;

use Tempest\Log\LogConfig;
use function Tempest\env;

$debugLogPath = env('LOG_PATH_DEBUG');
$serverLogPath = env('LOG_PATH_SERVER');

return new LogConfig(debugLogPath: $debugLogPath, serverLogPath: $serverLogPath);
