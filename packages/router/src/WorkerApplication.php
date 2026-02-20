<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\Container\Container;
use Tempest\Container\Singleton;
use Tempest\Core\Application;
use Tempest\Core\Kernel;
use Tempest\Core\Tempest;
use Tempest\Http\RequestFactory;
use Tempest\Http\RequestHolder;

#[Singleton]
final readonly class WorkerApplication implements Application
{
    public function __construct(
        private Container $container,
        private int $maxLoops = -1,
    ) {}

    /** @param \Tempest\Discovery\DiscoveryLocation[] $discoveryLocations */
    public static function boot(string $root, array $discoveryLocations = [], int $maxLoops = -1): self
    {
        $container = Tempest::boot($root, $discoveryLocations);

        return new self($container, $maxLoops);
    }

    public function run(): never
    {
        // Inspired from https://github.com/php-runtime/frankenphp-symfony/blob/main/src/Runner.php
        // Prevent worker script termination when a client connection is interrupted
        ignore_user_abort(true);

        $requestFactory = $this->container->get(RequestFactory::class);
        $responseSender = $this->container->get(ResponseSender::class);
        $router = $this->container->get(WorkerRouter::class);
        $requestHolder = $this->container->get(RequestHolder::class);

        $server = array_filter($_SERVER, static fn (string $key) => ! str_starts_with($key, 'HTTP_'), ARRAY_FILTER_USE_KEY);

        $handler = function () use ($server, $requestFactory, $responseSender, $router, $requestHolder): void {
            // Merge the environment variables coming from DotEnv with the ones tied to the current request
            $_SERVER += $server;

            $psrRequest = $requestFactory->make();
            $response = $router->dispatch($psrRequest);
            $responseSender->send($response);
            // TODO: this probably should be handled by RESET event I'm talking about below
            $requestHolder->clear();
        };

        $loops = 0;

        // it still allows to run application without frankenphp, but it will be a single request and then exit
        // this is only useful for testing and development
        if (! function_exists('frankenphp_handle_request')) {
            $handler();

            exit();
        }

        do {
            $ret = \frankenphp_handle_request($handler);

            // TODO: there should be some event to RESET state
            // for example: CookieManager should reset its internal cookies state after each request

            gc_collect_cycles();
        } while ($ret && (-1 === $this->maxLoops || ++$loops < $this->maxLoops));

        $this->container->get(Kernel::class)->shutdown();
    }
}
