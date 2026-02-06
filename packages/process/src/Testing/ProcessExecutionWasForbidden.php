<?php

namespace Tempest\Process\Testing;

use Exception;
use Tempest\Core\ProvidesContext;
use Tempest\Process\Exceptions\ProcessException;
use Tempest\Process\PendingProcess;

use function Tempest\Support\arr;

final class ProcessExecutionWasForbidden extends Exception implements ProcessException, ProvidesContext
{
    private function __construct(
        string $message,
        public readonly array $context = [],
    ) {
        parent::__construct($message);
    }

    public function context(): array
    {
        return $this->context;
    }

    public static function forPendingProcess(string|array|PendingProcess $process): self
    {
        $command = match (true) {
            $process instanceof PendingProcess => [$process->command],
            is_array($process) => $process,
            default => [$process],
        };

        return new self(
            message: sprintf('Process `%s` is being executed without a registered process result.', arr($command)->implode(' ')),
            context: ['process' => $process],
        );
    }

    public static function forPendingPool(iterable $pool): self
    {
        return new self(
            message: 'Process pool is being executed without a matching fake.',
            context: ['pool' => (array) $pool],
        );
    }
}
