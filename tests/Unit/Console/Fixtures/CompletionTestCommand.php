<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Console\Fixtures;

use Tempest\Console\ConsoleCommand;

final readonly class CompletionTestCommand
{
    #[ConsoleCommand('completion:test')]
    public function __invoke(string $value, bool $flag = false, array $items = [])
    {
        // TODO: Implement __invoke() method.
    }
}
