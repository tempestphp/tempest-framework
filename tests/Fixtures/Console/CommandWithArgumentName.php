<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Console;

use Tempest\Console\ConsoleArgument;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;

final readonly class CommandWithArgumentName
{
    use HasConsole;

    #[ConsoleCommand(name: 'command-with-argument-name')]
    public function __invoke(
        #[ConsoleArgument(name: 'new-name')]
        string $input,
        #[ConsoleArgument(name: 'new-flag')]
        bool $flag = false,
    ): void {
        $this->writeln($input);
        $this->writeln($flag ? 'true' : 'false');
    }
}
