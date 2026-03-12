<?php

declare(strict_types=1);

namespace Tempest\Core\Commands;

use Closure;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Container\Container;
use Tempest\Container\GenericContainer;
use Tempest\Core\FrameworkKernel;
use Tempest\Core\Kernel;
use Tempest\Discovery\BootDiscovery;
use Tempest\Discovery\DiscoveryCache;
use Tempest\Discovery\DiscoveryCacheStrategy;
use Tempest\Discovery\DiscoveryConfig;
use Tempest\Discovery\Registry;

if (class_exists(\Tempest\Console\ConsoleCommand::class)) {
    final readonly class DiscoveryGenerateCommand
    {
        use HasConsole;

        public function __construct(
            private Registry $registry,
            private FrameworkKernel $kernel,
            private DiscoveryCache $discoveryCache,
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

            $this->clearDiscoveryCache();

            $this->generateDiscoveryCache($strategy, fn () => null);
//            $this->console->task(
//                label: "Generating discovery cache using the `{$strategy->value}` strategy",
//                handler: fn (Closure $log) => $this->generateDiscoveryCache($strategy, $log),
//            );
        }

        public function clearDiscoveryCache(): void
        {
            $this->console->call(DiscoveryClearCommand::class);
        }

        public function generateDiscoveryCache(DiscoveryCacheStrategy $strategy, Closure $log): void
        {
            $kernel = $this->resolveKernel();

            $bootDiscovery = new BootDiscovery(
                container: $kernel->container,
                registry: $kernel->container->get(Registry::class),
                config: $kernel->container->get(DiscoveryConfig::class),
                cache: $this->discoveryCache,
            );

            $discoveries = $bootDiscovery->build();

            foreach ($this->registry->locations as $location) {
                $this->discoveryCache->store($location, $discoveries);
                $log($location->path);
            }

            $this->discoveryCache->storeStrategy($strategy);
        }

        public function resolveKernel(): Kernel
        {
            $container = new GenericContainer();
            $container->singleton(Container::class, $container);
            $container->singleton(Registry::class, $this->registry);

            return new FrameworkKernel(
                root: $this->kernel->root,
                discoveryLocations: $this->kernel->registry->locations,
                container: $container,
            )
                ->registerKernel()
                ->loadComposer()
                ->loadConfig();
        }
    }
}
