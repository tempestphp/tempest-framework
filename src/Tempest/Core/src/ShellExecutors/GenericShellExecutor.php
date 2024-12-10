<?php

declare(strict_types=1);

namespace Tempest\Core\ShellExecutors;

use Tempest\Core\ShellExecutor;

final class GenericShellExecutor implements ShellExecutor
{
    public function execute(string $command): void
    {
        shell_exec($command);
    }
}
