<?php

namespace Tempest\Core\Commands;

use Tempest\Cache\DiscoveryCacheStrategy;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Core\DiscoveryCache;
use Tempest\Core\Kernel;
use Tempest\Core\Kernel\LoadDiscoveryClasses;

final readonly class DiscoveryGenerateCommand
{
    use HasConsole;

    public function __construct(
        private Kernel $kernel,
        private DiscoveryCache $discoveryCache,
    ) {}

    #[ConsoleCommand(
        name: 'discovery:generate',
        description: 'Compile and cache all discovery according to the configured discovery caching strategy',
        aliases: ['dg'],
    )]
    public function __invoke(): void
    {
        $this->info('Clearing existing discovery cache…');

        $this->console->call('discovery:clear');

        $this->info('Generating new discovery cache…');

        $kernel = (new Kernel($this->kernel->root))
            ->registerKernel()
            ->loadComposer()
            ->loadDiscoveryLocations()
            ->loadConfig();

        $container = $kernel->container;

        $loadDiscoveryClasses = new LoadDiscoveryClasses(
            kernel: $kernel,
            container: $container,
            discoveryCache: $this->discoveryCache,
        );

        $discoveries = $loadDiscoveryClasses->build();

        foreach ($discoveries as $discovery) {
            $discoveryItems = $discovery->getItems();

            if ($this->discoveryCache->getStrategy() === DiscoveryCacheStrategy::PARTIAL) {
                $discoveryItems = $discoveryItems->onlyVendor();
            }

            $this->discoveryCache->store($discovery, $discoveryItems);

            $this->writeln(sprintf(
                '<success>%s</success> %d classes cached',
                $discovery::class,
                $discoveryItems->count(),
            ));
        }
    }
}