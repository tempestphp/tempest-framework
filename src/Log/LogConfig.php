<?php

declare(strict_types=1);

namespace Tempest\Log;

use Tempest\Log\Channels\AppendLogChannel;
use Tempest\Log\Channels\LogChannel;

final class LogConfig
{
    public function __construct(
        /** @var LogChannel[] */
        public array $channels = [],
        /** @var array<class-string<LogChannel>, array|string> */
        public array $channelsConfig = [],
        /** @var class-string<LogChannel> */
        public string $channel = AppendLogChannel::class,
        public string $prefix = 'tempest',
    ) {
    }
}
