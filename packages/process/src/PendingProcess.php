<?php

namespace Tempest\Process;

use Tempest\DateTime\Duration;

/**
 * Represents a process that has not been started yet.
 */
final class PendingProcess
{
    /**
     * @param array|string $command The command to run and its arguments listed as separate entries.
     * @param null|Duration $timeout Sets the process timeout (max. runtime).
     * @param null|Duration $idleTimeout Sets the process idle timeout (max. time since last output).
     * @param null|string $path Working directory for the process.
     * @param null|string $input Content that will be passed to the underlying process standard input.
     * @param null|bool $quietly Disables fetching output and error output from the underlying process.
     * @param null|bool $tty If set to `true`, forces enabling TTY mode.
     * @param array<string,mixed> $environment Environment variables to set for the process.
     * @param array<string,mixed> $options Underlying `proc_open` options.
     */
    public function __construct(
        private(set) array|string $command = [],
        private(set) ?Duration $timeout = null,
        private(set) ?Duration $idleTimeout = null,
        private(set) ?string $path = null,
        private(set) ?string $input = null,
        private(set) bool $quietly = false,
        private(set) bool $tty = false,
        private(set) array $environment = [],
        private(set) array $options = [],
    ) {}
}
