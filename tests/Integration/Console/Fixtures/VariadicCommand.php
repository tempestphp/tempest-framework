<?php

declare(strict_types=1);

namespace Tests\Tempest\Integration\Console\Fixtures;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;

final readonly class VariadicCommand
{
    use HasConsole;

    #[ConsoleCommand('variadic-string-argument')]
    public function string(string ...$input): void
    {
        $this->writeln(json_encode($input));
    }

    #[ConsoleCommand('variadic-integer-argument')]
    public function integer(int ...$input): void
    {
        $this->writeln(json_encode($input));
    }

    #[ConsoleCommand('variadic-backed-enum-argument')]
    public function backedEnum(TestStringEnum ...$input): void
    {
        $this->writeln(json_encode($input));
    }
}
