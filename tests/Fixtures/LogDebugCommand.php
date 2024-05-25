<?php

declare(strict_types=1);

namespace Tests\Tempest\Console\Fixtures;

use Tempest\Console\ConsoleCommand;
use function Tempest\lw;

final readonly class LogDebugCommand
{
    #[ConsoleCommand('log')]
    public function log(): void
    {
        lw(a: ['a' => 123], b: 'abc', time: time());
    }
}
