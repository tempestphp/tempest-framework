<?php

declare(strict_types=1);

namespace Tempest\Application;

use Tempest\AppConfig;
use Tempest\Container\Container;
use Tempest\Http\RequestFactory;
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

            // TODO: in _theory_ we could refactor this to use… PSR
            // Give me a minute to digest that though.
            $request = (new RequestFactory())->make();

            $responseSender = $this->container->get(ResponseSender::class);

            $responseSender->send(
                $router->dispatch($request),
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
