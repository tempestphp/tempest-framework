<?php

declare(strict_types=1);

namespace Tempest\Core;

interface ShellExecutor
{
    public function execute(string $command): void;
}
