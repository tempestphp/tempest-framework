<?php

declare(strict_types=1);

namespace Tempest\Core\Commands;

use Tempest\Cache\DiscoveryCacheStrategy;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Container\Container;
use Tempest\Container\GenericContainer;
use Tempest\Core\DiscoveryCache;
use Tempest\Core\Kernel;
use Tempest\Core\Kernel\LoadDiscoveryClasses;
use function Tempest\env;

final readonly class DiscoveryGenerateCommand
{
    use HasConsole;

    public const string CURRENT_DISCOVERY_STRATEGY = __DIR__ . '/../.cache/current_discovery_strategy';

    public function __construct(
        private Kernel $kernel,
        private DiscoveryCache $discoveryCache,
    ) {
    }

    #[ConsoleCommand(
        name: 'discovery:generate',
        description: 'Compile and cache all discovery according to the configured discovery caching strategy',
        aliases: ['dg'],
    )]
    public function __invoke(): void
    {
        $this->info('Clearing existing discovery cache…');

        $this->console->call('discovery:clear');

        $strategy = $this->resolveDiscoveryCacheStrategy();

        $this->info(sprintf('Generating new discovery cache… (%s)', $strategy->value));

        $kernel = $this->resolveKernel();

        $loadDiscoveryClasses = new LoadDiscoveryClasses(
            kernel: $kernel,
            container: $kernel->container,
            discoveryCache: $this->discoveryCache,
        );

        $discoveries = $loadDiscoveryClasses->build();

        foreach ($discoveries as $discovery) {
            $discoveryItems = $discovery->getItems();

            if ($strategy === DiscoveryCacheStrategy::PARTIAL) {
                $discoveryItems = $discoveryItems->onlyVendor();
            }

            $this->discoveryCache->store($discovery, $discoveryItems);

            $this->writeln(sprintf(
                '<success>%s</success> %d items cached',
                $discovery::class,
                $discoveryItems->count(),
            ));
        }
    }

    private function resolveDiscoveryCacheStrategy(): DiscoveryCacheStrategy
    {
        $strategy = DiscoveryCacheStrategy::make(env('DISCOVERY_CACHE'));

        // Store the current env variable
        $dir = dirname(self::CURRENT_DISCOVERY_STRATEGY);

        if (! is_dir($dir)) {
            mkdir($dir, recursive: true);
        }

        file_put_contents(self::CURRENT_DISCOVERY_STRATEGY, $strategy->value);

        return $strategy;
    }

    public function resolveKernel(): Kernel
    {
        $container = new GenericContainer();
        $container->singleton(Container::class, $container);

        return (new Kernel(
            root: $this->kernel->root,
            discoveryLocations: $this->kernel->discoveryLocations,
            container: $container,
        ))
            ->registerKernel()
            ->loadComposer()
            ->loadConfig();
    }
}
