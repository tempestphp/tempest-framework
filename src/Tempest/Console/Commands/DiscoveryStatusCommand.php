<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Core\Kernel;

final readonly class DiscoveryStatusCommand
{
    public function __construct(
        private Console $console,
        private Kernel $kernel,
    ) {
    }

    #[ConsoleCommand(
        name: 'discovery:status',
        description: 'List all discovery locations and discovery classes',
        aliases: ['ds'],
    )]
    public function __invoke(): void
    {
        $this->console->info('Loaded Discovery classes');

        foreach ($this->kernel->discoveryClasses as $discoveryClass) {
            $this->console->writeln('- ' . $discoveryClass);
        }

        $this->console->writeln();

        $this->console->info('Folders included in Tempest');

        foreach ($this->kernel->discoveryLocations as $discoveryLocation) {
            $this->console->writeln('- '. $discoveryLocation->path);
        }
    }
}
