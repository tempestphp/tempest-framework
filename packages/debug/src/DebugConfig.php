<?php

namespace Tempest\Debug;

final class DebugConfig
{
    /**
     * @param string $logPath The file path where debug logs will be written.
     */
    public function __construct(
        public readonly string $logPath,
    ) {}
}
