<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Fixtures;

use Tempest\Console\ConsoleCommand;

final readonly class CompletionTestCommand
{
    #[ConsoleCommand('completion:test')]
    public function __invoke(string $value, bool $flag = false, array $items = []): void
    {
        // TODO: Implement __invoke() method.
    }
}
