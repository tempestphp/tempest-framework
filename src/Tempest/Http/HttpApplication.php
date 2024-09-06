<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Container\Container;
use Tempest\Core\AppConfig;
use Tempest\Core\Application;
use Tempest\Core\Kernel;
use Tempest\Core\Tempest;
use function Tempest\env;
use Tempest\Log\Channels\AppendLogChannel;
use Tempest\Log\LogConfig;
use Tempest\Support\PathHelper;
use Throwable;

final readonly class HttpApplication implements Application
{
    public function __construct(private Container $container) {}

    /** @param \Tempest\Core\DiscoveryLocation[] $discoveryLocations */
    public static function boot(
        string $root,
        array $discoveryLocations = [],
    ): self
    {
        $container = Tempest::boot($root, $discoveryLocations);

        // Application,
        // TODO: can be refactored to resolve via the container
        $application = new HttpApplication($container);

        $container->singleton(Application::class, fn () => $application);

        $root = $container->get(Kernel::class)->root;

        // Application-specific setup
        $logConfig = $container->get(LogConfig::class);
        $logConfig->debugLogPath = PathHelper::make($root, '/log/debug.log');
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
        } catch (Throwable $throwable) {
            foreach ($this->container->get(AppConfig::class)->exceptionHandlers as $exceptionHandler) {
                $exceptionHandler->handle($throwable);
            }

            throw $throwable;
        }
    }
}
