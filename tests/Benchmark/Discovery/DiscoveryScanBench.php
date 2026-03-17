<?php

declare(strict_types=1);

namespace Tests\Tempest\Benchmark\Discovery;

use Generator;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\ParamProviders;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use Tempest\Container\Container;
use Tempest\Core\FrameworkKernel;
use Tempest\Discovery\BootDiscovery;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryCache;
use Tempest\Discovery\DiscoveryCacheStrategy;
use Tempest\Discovery\DiscoveryConfig;
use Tempest\Discovery\DiscoveryLocation;

final class DiscoveryScanBench
{
    private Container $container;

    /** @var DiscoveryLocation[] */
    private array $discoveryLocations;

    /** @var class-string<Discovery>[] */
    private array $discoveryClasses;

    private string $root;

    private DiscoveryConfig $discoveryConfig;

    public function __construct()
    {
        $this->root = dirname(__DIR__, 3);
        $kernel = FrameworkKernel::boot(root: $this->root);
        $this->container = $kernel->container;
        $this->discoveryConfig = $kernel->discoveryConfig;
        $this->discoveryLocations = $kernel->discoveryConfig->locations;
        $this->discoveryClasses = $kernel->discoveryConfig->classes;
    }

    private function createLoader(): BootDiscovery
    {
        return new BootDiscovery(
            container: $this->container,
            config: $this->discoveryConfig,
            cache: new DiscoveryCache(DiscoveryCacheStrategy::NONE),
        );
    }

    #[Iterations(5)]
    #[Revs(10)]
    #[Warmup(2)]
    public function benchFullDiscoveryScan(): void
    {
        $this->createLoader()->build(
            discoveryClasses: $this->discoveryClasses,
            discoveryLocations: $this->discoveryLocations,
        );
    }

    #[Iterations(5)]
    #[ParamProviders('providePackages')]
    #[Revs(10)]
    #[Warmup(2)]
    public function benchSinglePackageScan(array $params): void
    {
        $this->createLoader()->build(
            discoveryClasses: $this->discoveryClasses,
            discoveryLocations: [
                new DiscoveryLocation($params['namespace'], $params['path']),
            ],
        );
    }

    public function providePackages(): Generator
    {
        yield 'clock (small)' => [
            'namespace' => 'Tempest\\Clock\\',
            'path' => $this->root . '/packages/clock/src',
        ];

        yield 'console (large)' => [
            'namespace' => 'Tempest\\Console\\',
            'path' => $this->root . '/packages/console/src',
        ];

        yield 'router (large)' => [
            'namespace' => 'Tempest\\Router\\',
            'path' => $this->root . '/packages/router/src',
        ];
    }
}
