<?php

declare(strict_types=1);

namespace Tempest\Console;

interface ShellExecutor
{
    public function execute(string $compiledCommand): void;
}
