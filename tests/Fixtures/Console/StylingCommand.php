<?php

namespace Tests\Tempest\Fixtures\Console;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;

final readonly class StylingCommand
{
    use HasConsole;

    #[ConsoleCommand(name: 'test:style')]
    public function __invoke()
    {
        $this
            ->info('info')
            ->success('success')
            ->warning('warning')
            ->error('error');
    }
}