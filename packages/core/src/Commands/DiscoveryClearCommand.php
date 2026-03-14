<?php

declare(strict_types=1);

namespace Tempest\Core\Commands;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Discovery\ClearDiscoveryCache;
use Tempest\Discovery\DiscoveryCache;

if (class_exists(ConsoleCommand::class)) {
    final readonly class DiscoveryClearCommand
    {
        use HasConsole;

        public function __construct(
            private DiscoveryCache $discoveryCache,
            private ClearDiscoveryCache $clearDiscoveryCache,
        ) {}

        #[ConsoleCommand(
            name: 'discovery:clear',
            description: 'Clears all cached discovery files',
            aliases: ['d:c', 'dc'],
        )]
        public function __invoke(): void
        {
            $this->console->task(
                label: 'Clearing discovery cache',
                handler: fn () => ($this->clearDiscoveryCache)($this->discoveryCache),
            );
        }
    }
}
