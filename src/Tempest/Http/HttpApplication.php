<?php

declare(strict_types=1);

namespace Tempest\Http;

use Tempest\Container\Container;
use Tempest\Core\AppConfig;
use Tempest\Core\Application;
use Tempest\Core\Tempest;
use function Tempest\env;
use Tempest\Http\Exceptions\HttpExceptionHandler;
use Tempest\Log\Channels\AppendLogChannel;
use Tempest\Log\LogConfig;
use Tempest\Support\PathHelper;
use Throwable;

final readonly class HttpApplication implements Application
{
    public function __construct(
        private Container $container,
        private AppConfig $appConfig,
    ) {
    }

    public static function boot(string $root, ?AppConfig $appConfig = null): self
    {
        $container = Tempest::boot($root, $appConfig);

        // TODO: check if we need this
        $appConfig = $container->get(AppConfig::class);

        // Application,
        // TODO: can be refactored to resolve via the container
        $application = new HttpApplication(
            container: $container,
            appConfig: $appConfig,
        );

        $container->singleton(Application::class, fn () => $application);

        // Application-specific setup
        $logConfig = $container->get(LogConfig::class);
        $logConfig->debugLogPath = PathHelper::make($appConfig->root, '/log/debug.log');
        $logConfig->serverLogPath = env('SERVER_LOG');
        $logConfig->channels[] = new AppendLogChannel(PathHelper::make($appConfig->root, '/log/tempest.log'));
        $appConfig->exceptionHandlers[] = $container->get(HttpExceptionHandler::class);

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
            if (! $this->appConfig->enableExceptionHandling) {
                throw $throwable;
            }

            foreach ($this->appConfig->exceptionHandlers as $exceptionHandler) {
                $exceptionHandler->handle($throwable);
            }
        }
    }
}
