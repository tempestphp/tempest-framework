<?php

declare(strict_types=1);

namespace Tempest\Console\Scheduler;

use Tempest\Console\ShellExecutor;

final class NullShellExecutor implements ShellExecutor
{
    public ?string $executedCommand = null;

    public function execute(string $compiledCommand): void
    {
        $this->executedCommand = $compiledCommand;
    }
}
