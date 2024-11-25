<?php

declare(strict_types=1);

namespace Tempest\Core\Commands;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;

final readonly class DiscoveryGenerateCommand
{
    use HasConsole;

    public function __construct(
        private GenerateDiscovery $generateDiscovery,
    ) {
    }

    #[ConsoleCommand(
        name: 'discovery:generate',
        description: 'Compile and cache all discovery according to the configured discovery caching strategy',
        aliases: ['dg'],
    )]
    public function __invoke(): void
    {
        $this->info('Generating new discovery cache…');

        ($this->generateDiscovery)();

        $this->success('Done');
    }
}
