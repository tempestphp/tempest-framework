<?php

declare(strict_types=1);

namespace Tempest\Core\Commands;

use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Container\GenericContainer;
use Tempest\Core\FrameworkKernel;
use Tempest\Discovery\ClearDiscoveryCache;
use Tempest\Discovery\DiscoveryCache;
use Tempest\Discovery\DiscoveryCacheStrategy;
use Tempest\Discovery\DiscoveryConfig;
use Tempest\Discovery\GenerateDiscoveryCache;

if (class_exists(ConsoleCommand::class)) {
    final readonly class DiscoveryGenerateCommand
    {
        use HasConsole;

        public function __construct(
            private FrameworkKernel $kernel,
            private DiscoveryConfig $discoveryConfig,
            private DiscoveryCache $discoveryCache,
            private GenerateDiscoveryCache $generateDiscoveryCache,
            private ClearDiscoveryCache $clearDiscoveryCache,
        ) {}

        #[ConsoleCommand(
            name: 'discovery:generate',
            description: 'Compile and cache all discovery according to the configured discovery caching strategy',
            aliases: ['d:g', 'dg'],
        )]
        public function __invoke(): void
        {
            $strategy = DiscoveryCacheStrategy::resolveFromEnvironment();

            if ($strategy === DiscoveryCacheStrategy::NONE) {
                $this->info('Discovery cache disabled, nothing to generate.');

                return;
            }

            $this->console->task(
                label: 'Clearing discovery cache',
                handler: fn () => ($this->clearDiscoveryCache)($this->discoveryCache),
            );

            $this->console->task(
                label: "Generating {$strategy->value} discovery cache",
                handler: function () use ($strategy) {
                    $kernel = $this->resolveKernel();

                    ($this->generateDiscoveryCache)(
                        container: $kernel->container,
                        config: $this->discoveryConfig,
                        cache: $this->discoveryCache->withStrategy($strategy),
                    );
                },
            );
        }

        private function resolveKernel(): FrameworkKernel
        {
            return new FrameworkKernel(
                root: $this->kernel->root,
                discoveryLocations: $this->kernel->discoveryConfig->locations,
                container: new GenericContainer(),
            )
                ->registerKernel()
                ->loadComposer()
                ->loadDiscoveryConfig()
                ->loadConfig();
        }
    }
}
