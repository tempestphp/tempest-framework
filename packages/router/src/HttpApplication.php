<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\Container\Container;
use Tempest\Container\Singleton;
use Tempest\Core\Application;
use Tempest\Core\Kernel;
use Tempest\Core\Tempest;
use Tempest\Http\RequestFactory;
use Tempest\Http\Session\Session;

#[Singleton]
final readonly class HttpApplication implements Application
{
    public function __construct(
        private Container $container,
    ) {}

    /** @param \Tempest\Discovery\DiscoveryLocation[] $discoveryLocations */
    public static function boot(string $root, array $discoveryLocations = []): self
    {
        return Tempest::boot($root, $discoveryLocations)->get(HttpApplication::class);
    }

    public function run(): void
    {
        $router = $this->container->get(Router::class);

        $psrRequest = $this->container->get(RequestFactory::class)->make();

        $responseSender = $this->container->get(ResponseSender::class);

        $responseSender->send(
            $router->dispatch($psrRequest),
        );

        $this->container->get(Session::class)->cleanup();

        $this->container->get(Kernel::class)->shutdown();
    }
}
