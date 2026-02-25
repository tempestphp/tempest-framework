<?php

declare(strict_types=1);

namespace Tests\Tempest\Benchmark\Discovery;

use Generator;
use PhpBench\Attributes\Iterations;
use PhpBench\Attributes\ParamProviders;
use PhpBench\Attributes\Revs;
use PhpBench\Attributes\Warmup;
use Tempest\Container\Container;
use Tempest\Core\DiscoveryCache;
use Tempest\Core\DiscoveryCacheStrategy;
use Tempest\Core\DiscoveryConfig;
use Tempest\Core\FrameworkKernel;
use Tempest\Core\Kernel\LoadDiscoveryClasses;
use Tempest\Discovery\Discovery;
use Tempest\Discovery\DiscoveryLocation;

final class DiscoveryScanBench
{
    private Container $container;

    /** @var DiscoveryLocation[] */
    private array $discoveryLocations;

    /** @var class-string<Discovery>[] */
    private array $discoveryClasses;

    private string $root;

    public function __construct()
    {
        $this->root = dirname(__DIR__, 3);
        $kernel = FrameworkKernel::boot(root: $this->root);
        $this->container = $kernel->container;
        $this->discoveryLocations = $kernel->discoveryLocations;
        $this->discoveryClasses = $kernel->discoveryClasses;
    }

    private function createLoader(): LoadDiscoveryClasses
    {
        return new LoadDiscoveryClasses(
            container: $this->container,
            discoveryConfig: new DiscoveryConfig(),
            discoveryCache: new DiscoveryCache(DiscoveryCacheStrategy::NONE),
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
