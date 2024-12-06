<?php

declare(strict_types=1);

namespace Tests\Tempest\Fixtures\Console;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;

final readonly class StylingCommand
{
    use HasConsole;

    #[ConsoleCommand(name: 'test:style')]
    public function __invoke(): void
    {
        $this
            ->info('info')
            ->success('success')
            ->warning('warning')
            ->error('error');
    }
}
