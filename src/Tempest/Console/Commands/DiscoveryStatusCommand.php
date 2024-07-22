<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Framework\Application\AppConfig;

final readonly class DiscoveryStatusCommand
{
    public function __construct(
        private Console $console,
        private AppConfig $appConfig,
    ) {
    }

    #[ConsoleCommand(
        name: 'discovery:status',
        description: 'List all discovery locations and discovery classes'
    )]
    public function __invoke(): void
    {
        $this->console->info('Loaded Discovery classes');

        foreach ($this->appConfig->discoveryClasses as $discoveryClass) {
            $this->console->writeln('- ' . $discoveryClass);
        }

        $this->console->writeln();

        $this->console->info('Folders included in Tempest');

        foreach ($this->appConfig->discoveryLocations as $discoveryLocation) {
            $this->console->writeln('- '. $discoveryLocation->path);
        }
    }
}
