<?php

declare(strict_types=1);

namespace Tempest\Router;

use Tempest\Container\Container;
use Tempest\Container\Singleton;
use Tempest\Core\Application;
use Tempest\Core\ExceptionHandler;
use Tempest\Core\Kernel;
use Tempest\Core\ResetHandler;
use Tempest\Core\Tempest;
use Tempest\Http\RequestFactory;
use Tempest\Http\Session\OpaqueSession;
use Tempest\Http\Session\Session;

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
        self::register($container);

        return new self($container, $maxLoops);
    }

    private static function register(Container $container): void
    {
        $session = $container->get(OpaqueSession::class);
        $container->singleton(Session::class, $session);
    }

    public function run(): never
    {
        // Inspired from https://github.com/php-runtime/frankenphp-symfony/blob/main/src/Runner.php
        // Prevent worker script termination when a client connection is interrupted
        ignore_user_abort(true);

        $server = array_filter($_SERVER, static fn (string $key) => ! str_starts_with($key, 'HTTP_'), ARRAY_FILTER_USE_KEY);

        $requestFactory = $this->container->get(RequestFactory::class);
        $responseSender = $this->container->get(ResponseSender::class);
        $router = $this->container->get(WorkerRouter::class);
        $resetHandler = $this->container->get(ResetHandler::class);
        $exceptionHandler = $this->container->get(ExceptionHandler::class);

        $handler = function () use ($server, $requestFactory, $responseSender, $router, $exceptionHandler): void {
            // Merge the environment variables coming from DotEnv with the ones tied to the current request
            $_SERVER += $server;

            try {
                $psrRequest = $requestFactory->make();
                $response = $router->dispatch($psrRequest);
                $responseSender->send($response);
            } catch (\Throwable $exception) {
                $exceptionHandler->handle($exception);
            }
        };

        $loops = 0;

        // it still allows to run application without frankenphp, but it will be a single request and then exit
        // this is only useful for testing and development
        if (! function_exists('frankenphp_handle_request')) {
            $handler();
            $resetHandler->reset($this->container);
            $this->container->get(Kernel::class)->shutdown();

            exit();
        }

        do {
            $ret = \frankenphp_handle_request($handler);

            $resetHandler->reset($this->container);

            gc_collect_cycles();
        } while ($ret && (-1 === $this->maxLoops || ++$loops < $this->maxLoops));

        $this->container->get(Kernel::class)->shutdown();
    }
}
