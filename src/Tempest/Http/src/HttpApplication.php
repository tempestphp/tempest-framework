<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Container\Container;
use Tempest\Container\Singleton;
use Tempest\Core\AppConfig;
use Tempest\Core\Application;
use Tempest\Core\Kernel;
use Tempest\Core\Tempest;
use function Tempest\env;
use Tempest\Http\Session\Session;
use Tempest\Log\Channels\AppendLogChannel;
use Tempest\Log\LogConfig;
use Tempest\Support\PathHelper;
use Throwable;

#[Singleton]
final readonly class HttpApplication implements Application
{
    public function __construct(private Container $container)
    {
    }

    /** @param \Tempest\Core\DiscoveryLocation[] $discoveryLocations */
    public static function boot(
        string $root,
        array $discoveryLocations = [],
    ): self {
        $container = Tempest::boot($root, $discoveryLocations);

        $application = $container->get(HttpApplication::class);

        // Application-specific setup
        $logConfig = $container->get(LogConfig::class);
        $logConfig->debugLogPath = PathHelper::make($container->get(Kernel::class)->root, '/log/debug.log');
        $logConfig->serverLogPath = env('SERVER_LOG');
        $logConfig->channels[] = new AppendLogChannel(PathHelper::make($root, '/log/tempest.log'));

        return $application;
    }

    public function run(): void
    {
        try {
            $router = $this->container->get(Router::class);

            $psrRequest = (new RequestFactory())->make();

            $responseSender = $this->container->get(ResponseSender::class);

            $responseSender->send(
                $router->dispatch($psrRequest),
            );

            $this->container->get(Session::class)->cleanup();

            $this->container->get(Kernel::class)->shutdown();
        } catch (Throwable $throwable) {
            foreach ($this->container->get(AppConfig::class)->exceptionHandlers as $exceptionHandler) {
                $exceptionHandler->handle($throwable);
            }

            throw $throwable;
        }
    }
}
