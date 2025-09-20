<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Fixtures;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;

final readonly class VariadicCommand
{
    use HasConsole;

    #[ConsoleCommand('command-with-variadic-argument')]
    public function __invoke(string ...$input): void
    {
        $this->writeln(json_encode($input));
    }
}
