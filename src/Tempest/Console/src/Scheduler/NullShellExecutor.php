<?php

declare(strict_types=1);

namespace Tempest\Console\Scheduler;

use Tempest\Console\ShellExecutor;

class NullShellExecutor implements ShellExecutor
{
    public function execute(string $compiledCommand): void
    {

    }
}
