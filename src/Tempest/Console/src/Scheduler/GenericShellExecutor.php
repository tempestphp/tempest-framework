<?php

declare(strict_types=1);

namespace Tempest\Console\Scheduler;

use Tempest\Console\ShellExecutor;

final class GenericShellExecutor implements ShellExecutor
{
    public function execute(string $compiledCommand): void
    {
        shell_exec($compiledCommand);
    }
}
