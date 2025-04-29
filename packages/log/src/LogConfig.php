<?php

declare(strict_types=1);

namespace Tempest\Log;

use Tempest\Log\Channels\AppendLogChannel;

use function Tempest\root_path;

final class LogConfig
{
    public function __construct(
        /** @var LogChannel[] */
        public array $channels = [],
        public string $prefix = 'tempest',
        public ?string $debugLogPath = null,
        public ?string $serverLogPath = null,
    ) {
        $this->debugLogPath ??= root_path('/log/debug.log');

        if ($this->channels === []) {
            $this->channels[] = new AppendLogChannel(root_path('/log/tempest.log'));
        }
    }
}
