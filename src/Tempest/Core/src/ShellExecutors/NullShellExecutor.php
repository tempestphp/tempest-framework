<?php

declare(strict_types=1);

namespace Tempest\Core\ShellExecutors;

use Tempest\Core\ShellExecutor;

final class NullShellExecutor implements ShellExecutor
{
    public array $executedCommands = [];

    public function execute(string $command): void
    {
        $this->executedCommands[] = $command;
    }
}
