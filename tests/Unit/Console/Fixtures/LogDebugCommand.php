<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Console\Fixtures;

use Tempest\Console\ConsoleCommand;

final readonly class LogDebugCommand
{
    #[ConsoleCommand('log')]
    public function log(): void
    {
        lw(a: ['a' => 123], b: 'abc', time: time());
    }
}
