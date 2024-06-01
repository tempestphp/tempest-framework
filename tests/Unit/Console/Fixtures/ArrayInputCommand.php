<?php

declare(strict_types=1);

namespace Tests\Tempest\Unit\Console\Fixtures;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;

final readonly class ArrayInputCommand
{
    use HasConsole;

    #[ConsoleCommand('array_input')]
    public function __invoke(array $input): void
    {
        $this->writeln(json_encode($input));
    }
}
