<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Fixtures;

use Tempest\Console\ConsoleCommand;

final readonly class CompletionTestCommand
{
    #[ConsoleCommand('completion:test')]
    public function __invoke(
        string $value, // @mago-expect best-practices/no-unused-parameter
        bool $flag = false, // @mago-expect best-practices/no-unused-parameter
        array $items = [], // @mago-expect best-practices/no-unused-parameter
    ): void {
    }
}
