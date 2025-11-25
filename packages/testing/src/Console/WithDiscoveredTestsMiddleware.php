<?php

namespace Tempest\Testing\Console;

use Tempest\Console\ConsoleMiddleware;
use Tempest\Console\ConsoleMiddlewareCallable;
use Tempest\Console\ExitCode;
use Tempest\Console\Initializers\Invocation;
use Tempest\Container\Container;
use Tempest\Core\Composer;
use Tempest\Core\DiscoveryCache;
use Tempest\Core\DiscoveryConfig;
use Tempest\Core\FrameworkKernel;
use Tempest\Core\Kernel;
use Tempest\Core\Kernel\LoadDiscoveryClasses;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Support\Namespace\Psr4Namespace;
use Tempest\Testing\TestDiscovery;

final class WithDiscoveredTestsMiddleware implements ConsoleMiddleware
{
    public function __construct(
        private Composer $composer,
        private Kernel $kernel,
        private Container $container,
        private DiscoveryConfig $discoveryConfig,
        private DiscoveryCache $discoveryCache,
    ) {}

    public function __invoke(Invocation $invocation, ConsoleMiddlewareCallable $next): ExitCode|int
    {
        $discoveryLocations = $this->composer
            ->getDevNamespaces()
            ->map(fn (Psr4Namespace $namespace) => DiscoveryLocation::fromNamespace($namespace));

        $kernel = new FrameworkKernel(
            $this->kernel->root,
            $discoveryLocations->toArray(),
            $this->container,
        );

        new LoadDiscoveryClasses(
            kernel: $kernel,
            container: $this->container,
            discoveryConfig: $this->discoveryConfig,
            discoveryCache: $this->discoveryCache,
        )([
            TestDiscovery::class,
        ]);

        return $next($invocation);
    }
}