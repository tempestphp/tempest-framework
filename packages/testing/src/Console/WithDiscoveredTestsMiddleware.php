<?php

namespace Tempest\Testing\Console;

use Tempest\Console\ConsoleMiddleware;
use Tempest\Console\ConsoleMiddlewareCallable;
use Tempest\Console\ExitCode;
use Tempest\Console\Initializers\Invocation;
use Tempest\Container\Container;
use Tempest\Core\Composer;
use Tempest\Core\Kernel\LoadDiscoveryClasses;
use Tempest\Discovery\DiscoveryLocation;
use Tempest\Support\Namespace\Psr4Namespace;
use Tempest\Testing\Discovery\TestDiscovery;

use function Tempest\Support\arr;

final readonly class WithDiscoveredTestsMiddleware implements ConsoleMiddleware
{
    public function __construct(
        private Composer $composer,
        private Container $container,
    ) {}

    public function __invoke(Invocation $invocation, ConsoleMiddlewareCallable $next): ExitCode|int
    {
        $this->container->invoke(
            LoadDiscoveryClasses::class,
            discoveryClasses: [
                TestDiscovery::class,
            ],
            discoveryLocations: arr($this->composer->devNamespaces)
                ->map(fn (Psr4Namespace $namespace) => DiscoveryLocation::fromNamespace($namespace))
                ->toArray(),
        );

        return $next($invocation);
    }
}
