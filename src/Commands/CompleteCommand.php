<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Console\Input\ConsoleArgumentBag;

final readonly class CompleteCommand
{
    use HasConsole;

    #[ConsoleCommand(
        name: '_complete',
        description: 'Provide autocompletion',
        hidden: true,
    )]
    public function __invoke(
        array $input,
        int $current,
    ): void {
        $argumentBag = new ConsoleArgumentBag($input);
        
        dd($argumentBag, $input);
    }
}
