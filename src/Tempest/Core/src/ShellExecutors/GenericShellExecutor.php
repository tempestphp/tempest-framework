<?php

declare(strict_types=1);

namespace Tempest\Core\ShellExecutors;

use Tempest\Core\ShellExecutor;

final class GenericShellExecutor implements ShellExecutor
{
    public function execute(string $command): void
    {
        /** @phpstan-ignore-next-line */
        shell_exec($command);
    }
}
