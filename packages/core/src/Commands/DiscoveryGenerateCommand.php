<?php

declare(strict_types=1);

namespace Tempest\Core\Commands;

use Closure;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Container\Container;
use Tempest\Container\GenericContainer;
use Tempest\Core\AppConfig;
use Tempest\Core\DiscoveryCache;
use Tempest\Core\DiscoveryCacheStrategy;
use Tempest\Core\DiscoveryConfig;
use Tempest\Core\FrameworkKernel;
use Tempest\Core\Kernel;
use Tempest\Core\Kernel\LoadDiscoveryClasses;

use function Tempest\env;

if (class_exists(\Tempest\Console\ConsoleCommand::class)) {
    final readonly class DiscoveryGenerateCommand
    {
        use HasConsole;

        public function __construct(
            private Kernel $kernel,
            private DiscoveryCache $discoveryCache,
            private AppConfig $appConfig,
        ) {}

        #[ConsoleCommand(
            name: 'discovery:generate',
            description: 'Compile and cache all discovery according to the configured discovery caching strategy',
            aliases: ['d:g'],
        )]
        public function __invoke(): void
        {
            $strategy = DiscoveryCacheStrategy::make(env('DISCOVERY_CACHE', default: $this->appConfig->environment->isProduction()));

            if ($strategy === DiscoveryCacheStrategy::NONE) {
                $this->info('Discovery cache disabled, nothing to generate.');

                return;
            }

            $this->clearDiscoveryCache();

            $this->console->task(
                label: "Generating discovery cache using the `{$strategy->value}` strategy",
                handler: fn (Closure $log) => $this->generateDiscoveryCache($strategy, $log),
            );
        }

        public function clearDiscoveryCache(): void
        {
            $this->console->call(DiscoveryClearCommand::class);
        }

        public function generateDiscoveryCache(DiscoveryCacheStrategy $strategy, Closure $log): void
        {
            $kernel = $this->resolveKernel();

            $loadDiscoveryClasses = new LoadDiscoveryClasses(
                container: $kernel->container,
                discoveryConfig: $kernel->container->get(DiscoveryConfig::class),
                discoveryCache: $this->discoveryCache,
            );

            $discoveries = $loadDiscoveryClasses->build();

            foreach ($this->kernel->discoveryLocations as $location) {
                $this->discoveryCache->store($location, $discoveries);
                $log($location->path);
            }

            $this->discoveryCache->storeStrategy($strategy);
        }

        public function resolveKernel(): Kernel
        {
            $container = new GenericContainer();
            $container->singleton(Container::class, $container);

            return new FrameworkKernel(
                root: $this->kernel->root,
                discoveryLocations: $this->kernel->discoveryLocations,
                container: $container,
            )
                ->registerKernel()
                ->loadComposer()
                ->loadConfig();
        }
    }
}
