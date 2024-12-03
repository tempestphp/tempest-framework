<?php

declare(strict_types=1);

namespace Tempest\Core\Commands;

use Tempest\Console\Console;
use Tempest\Console\ConsoleCommand;
use Tempest\Core\DiscoveryCache;
use Tempest\Core\Kernel;

final readonly class DiscoveryStatusCommand
{
    public function __construct(
        private Console $console,
        private Kernel $kernel,
        private DiscoveryCache $discoveryCache,
    ) {
    }

    #[ConsoleCommand(
        name: 'discovery:status',
        description: 'List all discovery locations and discovery classes',
        aliases: ['ds'],
    )]
    public function __invoke(): void
    {
        $this->console->writeln('<h2>Registered discovery classes</h2>');

        foreach ($this->kernel->discoveryClasses as $discoveryClass) {
            $this->console->writeln('- ' . $discoveryClass);
        }

        $this->console->writeln();

        $this->console->writeln('<h2>Discovery locations loaded by Tempest</h2>');

        foreach ($this->kernel->discoveryLocations as $discoveryLocation) {
            $this->console->writeln('- '. $discoveryLocation->path);
        }

        $this->console
            ->writeln()
            ->when($this->discoveryCache->isEnabled(), fn (Console $console) => $console->success('Discovery cache enabled'))
            ->unless($this->discoveryCache->isEnabled(), fn (Console $console) => $console->error('Discovery cache disabled'));
    }
}
