<?php

declare(strict_types=1);

namespace Tempest\Console\Commands;

use Tempest\AppConfig;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\ConsoleOutput;

final readonly class DiscoveryStatusCommand
{
    public function __construct(
        private ConsoleOutput $output,
        private AppConfig $appConfig,
    ) {
    }

    #[ConsoleCommand(
        name: 'discovery:status',
        description: 'List all discovery locations and discovery classes'
    )]
    public function __invoke(): void
    {
        $this->output->info('Loaded Discovery classes');

        foreach ($this->appConfig->discoveryClasses as $discoveryClass) {
            $this->output->writeln('- ' . $discoveryClass);
        }

        $this->output->writeln('');

        $this->output->info('Folders included in Tempest');

        foreach ($this->appConfig->discoveryLocations as $discoveryLocation) {
            $this->output->writeln('- '. $discoveryLocation->path);
        }
    }
}
