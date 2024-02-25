<?php

declare(strict_types=1);

namespace Tempest\Application;

use Tempest\AppConfig;
use Tempest\Container\Container;
use Tempest\Http\Request;
use Tempest\Http\ResponseSender;
use Tempest\Http\Router;
use Throwable;

final readonly class HttpApplication implements Application
{
    public function __construct(
        private Container $container,
        private AppConfig $appConfig,
    ) {
    }

    public function run(): void
    {
        try {
            $router = $this->container->get(Router::class);
            $request = $this->container->get(Request::class);
            $responseSender = $this->container->get(ResponseSender::class);

            $responseSender->send(
                $router->dispatch($request),
            );
        } catch (Throwable $throwable) {
            foreach ($this->appConfig->exceptionHandlers as $exceptionHandler) {
                $exceptionHandler->handle($throwable);
            }
        }
    }
}
