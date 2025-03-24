<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Fixtures;

use Tempest\Console\ConsoleCommand;

final class MyConsole
{
    #[ConsoleCommand(name: 'test', description: 'description')]
    public function handle(
        string $path, // @mago-expect best-practices/no-unused-parameter
        TestStringEnum $type, // @mago-expect best-practices/no-unused-parameter
        TestStringEnum $fallback = TestStringEnum::A, // @mago-expect best-practices/no-unused-parameter
        int $times = 1, // @mago-expect best-practices/no-unused-parameter
        bool $force = false, // @mago-expect best-practices/no-unused-parameter
    ): void {
    }
}
