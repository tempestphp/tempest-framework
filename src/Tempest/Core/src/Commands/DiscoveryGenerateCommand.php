<?php

namespace Tempest\Core\Commands;

use Tempest\Cache\DiscoveryCacheStrategy;
use Tempest\Console\ConsoleCommand;
use Tempest\Console\HasConsole;
use Tempest\Core\DiscoveryCache;
use Tempest\Core\Kernel;
use Tempest\Core\Kernel\LoadDiscoveryClasses;
use function Tempest\root_path;
use function Tempest\Support\arr;
use function Tempest\Support\str;

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

        $strategy = $this->resolveDiscoveryCacheStrategy();

        $this->info(sprintf('Generating new discovery cache… (%s)', $strategy->value));

        $kernel = (new Kernel($this->kernel->root))
            ->registerKernel()
            ->loadComposer()
            ->loadDiscoveryLocations()
            ->loadConfig();

        // TODO: avoid container singleton
        $container = $kernel->container;

        $loadDiscoveryClasses = new LoadDiscoveryClasses(
            kernel: $kernel,
            container: $container,
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
        $possibleValues = arr(DiscoveryCacheStrategy::cases())
            ->map(fn (DiscoveryCacheStrategy $strategy) => $strategy->value)
            ->implode('|');

        $envPath = root_path('.env');

        if (is_file($envPath)) {
            $contents = file_get_contents($envPath);

            $cachingStrategyFromEnv = str($contents)->match('/DISCOVERY_CACHE=(' . $possibleValues . ')/')[1] ?? 'none';
        }

        $cachingStrategyFromEnv ??= 'none';

        return DiscoveryCacheStrategy::tryFrom($cachingStrategyFromEnv) ?? DiscoveryCacheStrategy::NONE;
    }
}