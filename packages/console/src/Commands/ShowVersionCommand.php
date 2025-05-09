<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Container\Container;
use Tempest\Core\Kernel;

final readonly class ShowVersionCommand
{
    use HasConsole;

    #[ConsoleCommand(
        name: 'version',
        description: 'Shows Tempest version',
        aliases: ['v'],
    )]
    public function __invoke(): void
    {
        $version = Kernel::VERSION;

        $this->console->header('Tempest version');
        $this->console->writeln('v' . $version);
    }
}
