<?php

declare(strict_types=1);

namespace Tempest\Console\Scheduler;

use Tempest\Console\ShellExecutor;

/** @phpstan-ignore-next-line  */
final class NullShellExecutor implements ShellExecutor
{
    public function execute(string $compiledCommand): void
    {

    }
}
